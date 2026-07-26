# SofizPay CIB Integration Guide (DiasZone Reference)

This document explains exactly how the current SofizPay (Baridimob CIB) payment flow is implemented in this project, so you can reproduce it in another platform.

## 1) What You Need

- A SofizPay merchant account (`SOFIZPAY_MERCHANT_ACCOUNT`).
- Your platform public URL (HTTPS).
- A return URL endpoint in your app where the user is redirected after paying.
- Order table + payment session table in your database.
- A provider/top-up service to run after payment is verified (in this project: Digiflazz/VIP Reseller).

## 2) Config and Environment

The payment config lives in `config/services.php` under `sofizpay`:

- `enabled`: turn SofizPay on/off.
- `sandbox`: switch between sandbox and production paths.
- `base_url`: default `https://sofizpay.com`.
- `merchant_account`: your SofizPay account identifier.
- `timeout`: HTTP timeout seconds.
- `redirect`: keep `"no"` for server-side create (important; `"yes"` may return HTML redirect instead of JSON).
- `keep_return_url`: default `"True"`.

Expected env variables:

- `SOFIZPAY_ENABLED=true`
- `SOFIZPAY_SANDBOX=false`
- `SOFIZPAY_BASE_URL=https://sofizpay.com`
- `SOFIZPAY_MERCHANT_ACCOUNT=YOUR_ACCOUNT`
- `SOFIZPAY_TIMEOUT=30`
- `SOFIZPAY_REDIRECT=no`
- `SOFIZPAY_KEEP_RETURN_URL=True`

## 3) Routes

Core routes in `routes/web.php`:

- `POST /api/baridimob/process` -> create SofizPay transaction (`processBaridimobPayment`).
- `GET /payment/sofizpay/cib/return` -> verify payment (`sofizpayCibReturn`).
- `GET /select/bmccp/{encrypted_order_id}` -> Baridimob form entry.
- `GET /payment/success/{encrypted_order_id}` -> success page.

Also used for seller storefront:

- Storefront flow creates SofizPay transaction in `SellerStorefrontController::processPayment`, but still returns to the same `payment.sofizpay.cib.return` route.

## 4) Data Model (Critical)

Migration: `database/migrations/2026_04_16_120000_create_sofizpay_cib_transactions_table.php`

### Table: `sofizpay_cib_transactions`

- `order_id` (unique FK -> `orders`)
- `transaction_id` (SofizPay UUID from create API)
- `cib_order_number` (used for check API)
- `cib_order_id` (SATIM `orderId` / `mdOrder` if present)
- `amount_expected` (decimal)
- `status` (`pending`, `paid`, ...)
- `create_response` (JSON)
- `last_check_response` (JSON)
- `paid_at` timestamp

### Orders table link

- `orders.sofizpay_cib_transaction_id` nullable FK -> `sofizpay_cib_transactions`.

### Eloquent models

- `App\Models\SofizPayCibTransaction`
- `App\Models\Order::sofizpayCibTransaction()`

## 5) Service Layer (API Wrapper)

Service file: `app/Services/SofizPayCibService.php`

Implemented methods:

- `createPath()` -> `/make-cib-transaction/` or `/sandbox/make-cib-transaction/`
- `checkPath()` -> `/cib-transaction-check/` or `/sandbox/cib-transaction-check/`
- `createCibTransaction(array $queryParams)` -> GET create endpoint, expect JSON with `success=true` and `payment_url`.
- `checkCibTransaction(string $orderNumber)` -> GET check endpoint.
- `isPaidCheck(array $data)` -> paid only when:
  - `respCode === '00'`
  - `errorCode == 0`
  - `orderStatus == 2`
- `parsePaymentFailureHint()` -> extracts a readable failure reason from gateway fields.
- `parsePaidAmountDzd()` -> reads paid amount (`Amount`/`amount`).
- `parseDestinationAccount()` -> reads destination account to verify merchant match.

## 6) Payment Creation Flow (Server Side)

Main implementation: `CheckoutController::processBaridimobPayment`.

Step-by-step:

1. Decrypt and load order (`encrypted_order_id` -> order).
2. Ensure SofizPay config is enabled and merchant exists.
3. Compute amount with `calculateOrderBaridimobAmountDzd()`:
   - Handles single-pack and multi-item orders.
   - Handles coupons/storefront final prices where needed.
4. Enforce minimum amount (`>= 75 DZD`).
5. Create/update local `Bmccp` record and set order status `pending_bmccp`.
6. Build return URL:
   - `route('payment.sofizpay.cib.return').'?eid='.encrypt(order_id)`
7. Call SofizPay create endpoint with query:
   - `account`, `amount`, `full_name`, `phone`, `email`, `return_url`, `memo`, `redirect`, `keep_return_url`.
8. Validate response has `payment_url`.
9. Store SofizPay session in `sofizpay_cib_transactions`.
10. Link `orders.sofizpay_cib_transaction_id`.
11. Return `checkout_url` to frontend so browser redirects user to bank page.

## 7) Return URL Verification Flow (Most Important)

Implementation: `CheckoutController::sofizpayCibReturn`.

Never trust return redirect alone. This flow verifies on server:

1. Read `eid`, decrypt to `order_id`.
2. Load order + linked SofizPay transaction.
3. Short-circuit if already paid / already processed.
4. Ensure order status still payable (`pending_bmccp` or `pending`).
5. Read `cib_order_number` from saved session.
6. Call SofizPay check endpoint (`checkCibTransaction`).
7. Save raw check response in DB (`last_check_response`).
8. Require `isPaidCheck(...) == true`.
9. Validate amounts:
   - Paid amount from gateway vs session amount (`amount_expected`).
   - Recomputed canonical order amount vs session amount.
   - Mismatch tolerance in this code is `> 1.0 DZD`.
10. Validate destination account equals configured merchant account (if provided by gateway).
11. In DB transaction + row lock:
    - Mark SofizPay session `paid`.
    - Set `paid_at`.
    - Move order status to `sending`.
    - Approve linked BMCCP record.
12. Trigger top-up/recharge (`processBaridimobPaidRecharge`).
13. Redirect to correct success page.

### Idempotency protections used

- Checks if SofizPay session already `paid`.
- Checks if order already `completed`/`sending`.
- Uses `lockForUpdate()` on both `orders` and `sofizpay_cib_transactions`.
- Runs recharge only once via guarded state transition.

## 8) Recharge Triggering

After payment verification, this project immediately starts recharge:

- Method: `processBaridimobPaidRecharge($order)`.
- Updates order/provider statuses and logs.
- Telegram message is updated to processing state.

If you reimplement on another platform, keep this boundary:

- Payment verification (financial truth) first.
- Fulfillment/top-up second.
- Fulfillment should be retryable and independently observable.

## 9) Error Handling Strategy

Creation flow handles:

- Timeout/network errors (returns user-friendly temporary outage message).
- Auth/config errors (e.g., 401 from gateway).
- Non-JSON and malformed responses.

Return flow handles:

- Invalid/expired encrypted order token.
- Missing payment session.
- Payment not yet confirmed (retry message to customer).
- Decline/failure hints from provider.
- Amount/account mismatch (critical, ask support).

## 10) Webhook Note

`baridimobWebhook` is intentionally disabled (returns `410`), because this integration confirms payment on the return URL by calling SofizPay check endpoint server-to-server.

So in this implementation, **the return verification endpoint is the source of truth**, not a Chargily-style webhook.

## 11) Storefront Variant

In `SellerStorefrontController::processPayment`, SofizPay flow is reused with seller-specific order creation:

- Order is created with seller data and `payment_method = baridimob`.
- SofizPay session is created the same way.
- Same return route is used.
- Success redirect is seller-specific.

## 12) Test Coverage You Can Reuse

Feature test: `tests/Feature/SofizPayCibReturnMultiQuantityTest.php`

It verifies:

- Paid return check marks order correctly.
- Multi-quantity order triggers multiple provider calls.
- Redirect goes to success route.

When migrating to another platform, replicate these test types:

- Paid return success.
- Not-paid return retry path.
- Amount mismatch rejection.
- Idempotent double-return behavior.

## 13) Implementation Checklist (Portable)

1. Add env/config keys.
2. Create payment session table + FK on orders.
3. Build SofizPay service wrapper (create/check endpoints).
4. Implement create-payment endpoint:
   - Save session, return `payment_url`.
5. Implement return endpoint:
   - Decrypt order token.
   - Check payment server-side.
   - Validate amount + merchant.
   - Atomic transition to paid/sending.
6. Trigger fulfillment after verified payment.
7. Add logs for every critical decision.
8. Add feature tests for success, pending, mismatch, idempotency.

## 14) Practical Tips Learned From This Integration

- Keep `SOFIZPAY_REDIRECT=no` for backend API flow.
- Always persist provider raw responses (`create_response`, `last_check_response`) for support/debugging.
- Use an encrypted order token in return URL (`eid`) instead of raw order IDs.
- Never mark paid from frontend/browser signals alone.
- Guard state changes with DB locks to avoid double-processing.

