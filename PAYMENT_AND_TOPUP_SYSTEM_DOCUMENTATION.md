# Payment and Top-Up System Documentation

## Overview
This document provides a comprehensive overview of the payment and top-up processes in DiasZone, covering Digiflazz integration, webhooks, status management, and Flexy payment flow.

---

## Table of Contents
1. [Digiflazz Top-Up Process](#1-digiflazz-top-up-process)
2. [Webhook System](#2-webhook-system)
3. [Status Update Mechanism](#3-status-update-mechanism)
4. [Database Tables](#4-database-tables)
5. [Flexy Payment Flow](#5-flexy-payment-flow)

---

## 1. Digiflazz Top-Up Process

### 1.1 Overview
Digiflazz is the primary provider used for game top-ups. The system sends order requests to Digiflazz API and receives status updates via webhooks.

### 1.2 Configuration
**Environment Variables:**
```env
DIGIFLAZZ_USERNAME=your_username
DIGIFLAZZ_SIGN=your_api_key
DIGIFLAZZ_BASE_URL=https://api.digiflazz.com/v1
DIGIFLAZZ_WEBHOOK_SECRET=your_webhook_secret
```

**Service Class:** `app/Services/DigiflazzService.php`

### 1.3 Order Placement Flow

#### Step 1: Order Creation
- Order is created in `orders` table with status `pending_flexy`, `pending_bmccp`, or `pending_cryptopay`
- Order contains game-specific IDs (user_id_ml, zone_id_ml, player_id_ff, etc.)
- Order is linked to a `diamond_pack` which contains the Digiflazz SKU code

#### Step 2: Triggering Digiflazz Top-Up
**Entry Points:**
1. **Admin Approval** (`AdminController::updateOrderStatus`)
   - Admin changes order status from `pending_confirmation` → `completed`
   - For Flexy orders with `flexy_id` set
   
2. **Chargily Payment Success** (`CheckoutController::handleChargilyWebhook`)
   - After successful Chargily payment
   - Order status: `pending_bmccp` → `sending`
   
3. **Direct Seller Top-Up** (`SellerController::directTopup`)
   - Seller initiates top-up directly
   - Order status: `processing` → `sending`

#### Step 3: DigiflazzService::placeOrder()

**Location:** `app/Services/DigiflazzService.php::placeOrder()`

**Process:**
```php
// Note: Duplicate check is handled atomically in calling code (CheckoutController/AdminController)
// with proper DB transaction locking. This prevents race conditions in multi-quantity loops.

// 2. Determine customer_no (game player ID)
// Mobile Legends: user_id_ml + zone_id_ml concatenated (e.g., "2057629734048")
// Free Fire: player_id_ff
// Other games: respective player ID fields
$customerNo = (string)$order->user_id_ml . (string)$order->zone_id_ml; // ML example

// 3. Generate ref_id (unique reference)
$refId = 'order-' . $order->id . '-' . Str::random(8);

// 4. Compute signature
$sign = md5($username . $sign . $refId);

// 5. Prepare payload
$payload = [
    'username' => $this->username,
    'buyer_sku_code' => $pack->code,  // SKU code from diamond_packs table
    'customer_no' => (string)$customerNo,
    'ref_id' => $refId,
    'sign' => $sign,
];

// 6. Send POST request to Digiflazz
POST https://api.digiflazz.com/v1/transaction
```

**Response Handling:**
```php
// Digiflazz returns:
{
    "data": {
        "trxid": "12345",
        "ref_id": "order-123-abc123",
        "customer_no": "2057629734048",
        "status": "sukses" | "pending" | "gagal",
        "rc": "00" | "03" | "99",
        "message": "...",
        "price": 10000,
        "sn": "serial_number",
        "buyer_last_saldo": 500000
    }
}
```

#### Step 4: Initial Status Record Creation
After successful API call, system creates/updates `digiflazz_statuses` record:

```php
DigiflazzStatus::updateOrCreate(
    ['ref_id' => $refId],
    [
        'order_id' => $order->id,
        'ref_id' => $refId,
        'trxid' => $json['data']['trxid'],
        'buyer_sku_code' => $json['data']['buyer_sku_code'],
        'customer_no' => $json['data']['customer_no'],
        'rc' => $json['data']['rc'],
        'status' => $json['data']['status'],
        'message' => $json['data']['message'],
        'price' => $json['data']['price'],
        'sn' => $json['data']['sn'],
        'additional_data' => $json,
        'event' => 'create'
    ]
);
```

#### Step 5: Order Status Update
After initial submission:
- Order status set to `sending` (if not using webhook-based status)
- Telegram notification sent (if configured)

---

## 2. Webhook System

### 2.1 Webhook Endpoint
**Route:** `POST /webhook/digiflazz`  
**Controller:** `app/Http/Controllers/Webhook/DigiflazzWebhookController.php`  
**Method:** `handle()`

### 2.2 Webhook Flow

#### Step 1: Webhook Reception
```php
// Digiflazz sends POST request to webhook URL
Headers:
- X-Digiflazz-Event: "create" | "update" | "status"
- X-Hub-Signature: sha1=HMAC_SHA1(payload, secret)

Body:
{
    "data": {
        "ref_id": "order-123-abc123",
        "trxid": "12345",
        "trx_id": "12345",  // Alternative key
        "customer_no": "2057629734048",
        "buyer_sku_code": "MLBB_500",
        "status": "sukses",
        "rc": "00",
        "message": "Top-up successful",
        "price": 10000,
        "sn": "serial_number",
        "buyer_last_saldo": 500000
    }
}
```

#### Step 2: Signature Validation
```php
$secret = env('DIGIFLAZZ_WEBHOOK_SECRET');
$signatureHeader = $request->header('X-Hub-Signature');
$raw = $request->getContent();
$expected = 'sha1=' . hash_hmac('sha1', $raw, $secret);

if (!hash_equals($expected, $signatureHeader)) {
    return response()->json(['error' => 'Invalid signature'], 403);
}
```

#### Step 3: Status Record Update/Creation
```php
// Extract data from payload
$refId = $payload['ref_id'] ?? null;
$trxId = $payload['trxid'] ?? $payload['trx_id'] ?? null;
$customerNo = $payload['customer_no'] ?? null;
$status = $payload['status'] ?? null;
$rc = $payload['rc'] ?? null;

// Find existing record by ref_id or trxid
$statusRecord = DigiflazzStatus::where('ref_id', $refId)
    ->orWhere('trxid', $trxId)
    ->first();

// Update or create
if ($statusRecord) {
    $statusRecord->update($data);
} else {
    $statusRecord = DigiflazzStatus::create($data);
}
```

#### Step 4: Order Matching (Clean Approach - 100% Guaranteed)

**Priority 1: ref_id Parsing (PRIMARY METHOD - GUARANTEED)**
```php
// Extract order ID from ref_id (format: "order-123-abc123")
// This is the PRIMARY and GUARANTEED method since we control ref_id generation
if (preg_match('/^order-(\d+)-/', $refId, $matches)) {
    $orderId = (int)$matches[1];
    $order = Order::find($orderId);
    // Direct match - no ambiguity possible
}
```

**Priority 2: customer_no Matching (FALLBACK ONLY)**
Only used if ref_id doesn't contain order ID pattern. Includes:
- Time window filter (only matches orders created within last 24 hours)
- Status priority (prefers orders in 'sending', 'pending', etc.)
- Multiple candidate detection with warnings
- Direct numeric order ID
- Mobile Legends patterns (user.zone, concatenated user+zone)
- Player ID fields (Free Fire, PUBG, Honor of Kings, Blood Strike)

#### Step 5: VipResellerStatus Mirror Creation
For backward compatibility, creates a mirror record in `vip_reseller_statuses`:

```php
VipResellerStatus::updateOrCreate(
    ['trxid' => $trxId],
    [
        'order_id' => $statusRecord->order_id,
        'trxid' => $trxId,
        'status' => $normalizedStatus,  // 'success' | 'waiting' | 'error'
        'note' => $payload['message'],
        'price' => $payload['price'],
        'balance' => $payload['buyer_last_saldo'],
        'additional_data' => $payload,
    ]
);
```

---

## 3. Status Update Mechanism

### 3.1 Status Mapping

**Digiflazz Status → Order Status:**

```php
// Location: DigiflazzWebhookController::applyStatusToOrder()

$status = strtolower($statusRecord->status ?? '');
$rc = $statusRecord->rc ?? null;

// Success Cases
if ($status === 'sukses' || $rc === '00') {
    // For multi-quantity orders (e.g., 3x Weekly Pass)
    if ($order->quantity > 1) {
        $succeeded = $order->successfulDigiflazzTopupsCount();
        if ($succeeded >= $order->quantity) {
            $order->status = 'completed';
        } else {
            $order->status = 'sending';
            // Append progress: "Digiflazz: 2/3 top-ups completed"
        }
    } else {
        $order->status = 'completed';
    }
}
// Pending Cases
elseif ($status === 'pending' || in_array($rc, ['03', '99'])) {
    $order->status = 'sending';
}
// Failure Cases
else {
    $order->status = 'failed';
}
```

### 3.2 Order Status Lifecycle

**Complete Flow:**
```
pending_flexy / pending_bmccp / pending_cryptopay
    ↓
pending_confirmation (Flexy only - after receipt upload)
    ↓
processing / sending
    ↓
completed / failed
```

**Status Definitions:**
- `pending_flexy`: Waiting for Flexy payment receipt
- `pending_bmccp`: Waiting for Baridimob/Chargily payment
- `pending_cryptopay`: Waiting for crypto payment
- `pending_confirmation`: Flexy receipt uploaded, waiting admin approval
- `processing`: Order being processed
- `sending`: Top-up submitted to provider, awaiting confirmation
- `completed`: Top-up successfully delivered
- `failed`: Top-up failed

### 3.3 Multi-Quantity Orders
For orders with `quantity > 1` (e.g., 3x Weekly Pass):

```php
// Count successful top-ups
$succeeded = $order->digiflazzStatuses()
    ->where(function ($q) {
        $q->whereRaw("LOWER(status) = 'sukses'")
          ->orWhere('rc', '00');
    })->count();

// Only mark completed when all top-ups succeed
if ($succeeded >= $order->quantity) {
    $order->status = 'completed';
}
```

### 3.4 Status Update Side Effects

**On Order Completion:**
1. **Seller Profit Credit** (if applicable)
   ```php
   if ($order->seller_id && !$order->seller_profit_paid) {
       $order->creditSellerProfit();
       // Credits seller wallet with $order->seller_profit
   }
   ```

2. **Telegram Notification Update**
   ```php
   // Update existing Telegram message or send new one
   TelegramService::editMessageText($order->tlg_message_id, $message);
   // or
   TelegramService::sendMessage($message);
   ```

3. **Order Notes Appended**
   ```php
   $order->notes = trim($order->notes . "\nDigiflazz: " . $statusRecord->message);
   ```

---

## 4. Database Tables

### 4.1 orders Table

**Key Columns:**
```sql
- id (primary key)
- order_number (unique: "ORD-XXXXX")
- user_id (nullable, foreign key to users)
- seller_id (nullable, foreign key to sellers)
- diamond_pack_id (foreign key to diamond_packs)
- status (enum: pending_flexy, pending_bmccp, pending_cryptopay, pending_confirmation, processing, sending, completed, failed)
- quantity (integer, default 1)
- flexy_id (nullable, foreign key to flexies)
- bmccp_id (nullable, foreign key to bmccps)
- cryptopay_id (nullable)
- chargily_status_id (nullable)
- user_id_ml, zone_id_ml (Mobile Legends)
- player_id_ff (Free Fire)
- player_id_pubg (PUBG Mobile)
- player_id_hok (Honor of Kings)
- user_id_bs, server_bs (Blood Strike)
- original_price, final_price (after discount)
- seller_cost, seller_profit, seller_profit_paid
- tlg_message_id (Telegram message ID)
- notes (text)
- created_at, updated_at
```

### 4.2 digiflazz_statuses Table

**Purpose:** Stores all Digiflazz API responses and webhook updates

**Key Columns:**
```sql
- id (primary key)
- order_id (nullable, foreign key to orders)
- ref_id (unique, e.g., "order-123-abc123")
- trxid (transaction ID from Digiflazz)
- buyer_sku_code (SKU code)
- customer_no (game player ID)
- rc (response code: "00" = success, "03"/"99" = pending)
- status (sukses, pending, gagal)
- message (response message)
- price (transaction price)
- sn (serial number)
- additional_data (JSON, full payload)
- event (create, update, status)
- created_at, updated_at
```

**Indexes:**
- `ref_id` (unique)
- `trxid` (indexed)
- `order_id` (foreign key)

### 4.3 vip_reseller_statuses Table

**Purpose:** Legacy table for backward compatibility, mirrors Digiflazz statuses

**Key Columns:**
```sql
- id (primary key)
- order_id (nullable, foreign key to orders)
- trxid (transaction ID)
- status (success, waiting, error)
- note (message)
- price (transaction price)
- balance (provider balance)
- additional_data (JSON)
- created_at, updated_at
```

### 4.4 flexies Table

**Purpose:** Stores Flexy payment receipts

**Key Columns:**
```sql
- id (primary key)
- diamond_pack_id (foreign key)
- receipt_image (path to uploaded image)
- status (pending, approved, rejected)
- notes (text)
- created_at, updated_at
```

**Relationships:**
- `orders.flexy_id` → `flexies.id`

---

## 5. Flexy Payment Flow

### 5.1 Overview
Flexy is a manual payment method where users upload payment receipts. Admins manually approve payments before top-up processing begins.

### 5.2 Flow Diagram

```
User selects Flexy payment
    ↓
Order created (status: pending_flexy)
    ↓
User redirected to Flexy form page
    ↓
User uploads receipt image
    ↓
POST /select/flexy (submitFlexy)
    ↓
Flexy record created
Order status → pending_confirmation
    ↓
Admin reviews receipt (via dashboard or Telegram)
    ↓
Admin approves (updateOrderStatus)
Order status → completed
    ↓
DigiflazzService::placeOrder() called
Order status → sending
    ↓
Digiflazz webhook updates status
Order status → completed (on success)
```

### 5.3 Detailed Steps

#### Step 1: Order Creation
```php
// CheckoutController::createOrder()
$order = Order::create([
    'order_number' => Order::generateOrderNumber(),
    'diamond_pack_id' => $packId,
    'status' => 'pending_flexy',  // ← Flexy-specific status
    'user_id' => auth()->id(),
    // ... game IDs, prices, etc.
]);
```

#### Step 2: Flexy Form Page
**Route:** `GET /select/flexy?order_id={encrypted}`  
**View:** `resources/views/pages/flexy-form.blade.php`

**Features:**
- Shows order summary (game, pack, IDs, prices)
- Displays Flexy phone number: **0673771763** (prominently)
- Shows processing fee: **50 DZD**
- File upload field (max 5MB, images only)
- Optional notes field
- reCAPTCHA verification

#### Step 3: Receipt Submission
**Route:** `POST /select/flexy`  
**Controller:** `CheckoutController::submitFlexy()`

**Validations:**
```php
- encrypted_order_id (required)
- receipt_image (required, image, max 5MB, mimes: jpeg,png,jpg,gif,webp)
- notes (nullable, max 1000 chars)
- reCAPTCHA verification
```

**Process:**
```php
// 1. Decrypt order ID
$orderId = Crypt::decryptString($request->input('encrypted_order_id'));

// 2. Validate order
$order = Order::find($orderId);
// Check: order exists, belongs to user, status is 'pending_flexy'

// 3. Store receipt image
$storagePath = public_path('storage/flexy_receipts');
$filename = $order->id . '_' . time() . '_' . sanitized_name;
$file->move($storagePath, $filename);
$imagePath = 'storage/flexy_receipts/' . $filename;

// 4. Create Flexy record
$flexy = Flexy::create([
    'receipt_image' => $imagePath,
    'diamond_pack_id' => $order->diamond_pack_id,
    'status' => 'pending',
]);

// 5. Link to order and update status
$order->flexy_id = $flexy->id;
$order->status = 'pending_confirmation';  // ← Waiting for admin approval
$order->notes = $request->input('notes');
$order->save();

// 6. Send Telegram notification (with approve/reject buttons)
TelegramService::sendMessage($message, $withButtons = true);
```

#### Step 4: Admin Approval

**Route:** `POST /adm/orders/{orderNumber}/status`  
**Controller:** `AdminController::updateOrderStatus()`

**Trigger Conditions:**
```php
// Must meet ALL conditions:
1. Old status === 'pending_confirmation'
2. New status === 'completed'
3. Order has flexy_id (not null)
4. Request from admin dashboard
```

**Approval Process:**
```php
if ($oldStatus === 'pending_confirmation' && 
    $newStatus === 'completed' && 
    $order->flexy_id) {
    
    // 1. Update order status to 'sending'
    $order->status = 'sending';
    $order->save();
    
    // 2. Call DigiflazzService::placeOrder()
    $digService = app(DigiflazzService::class);
    $result = $digService->placeOrder($order->diamondPack, $order);
    
    // 3. Create DigiflazzStatus record (from API response)
    DigiflazzStatus::updateOrCreate([...]);
    
    // 4. Update order status based on API response
    // (If immediate success, set to 'completed')
    // (Otherwise, wait for webhook)
}
```

#### Step 5: Webhook Updates
After admin approval and Digiflazz submission, webhook continues to update status:
- Webhook receives status updates from Digiflazz
- `DigiflazzWebhookController::applyStatusToOrder()` updates order status
- Order moves: `sending` → `completed` (on success)

### 5.4 Admin Interface

**Flexy Approvals Page:**
**Route:** `GET /adm/flexy-approvals`  
**View:** `resources/views/admin/flexy-approvals.blade.php`

**Features:**
- Lists orders with status `pending_flexy_verification`
- Shows receipt images
- Approve/Reject buttons

**Actions:**
- **Approve:** Calls `approveFlexy()` → processes order → calls Digiflazz
- **Reject:** Calls `rejectFlexy()` → sets order status to `failed`

---

## Summary

### Key Files

**Digiflazz Service:**
- `app/Services/DigiflazzService.php` - API integration

**Webhook Handler:**
- `app/Http/Controllers/Webhook/DigiflazzWebhookController.php` - Webhook processing

**Order Management:**
- `app/Http/Controllers/CheckoutController.php` - Order creation, Flexy submission
- `app/Http/Controllers/AdminController.php` - Admin approval, status updates

**Models:**
- `app/Models/Order.php` - Order model
- `app/Models/DigiflazzStatus.php` - Digiflazz status model
- `app/Models/Flexy.php` - Flexy payment model

**Database Migrations:**
- `database/migrations/2025_12_11_000000_create_digiflazz_statuses_table.php`
- `database/migrations/2025_12_06_add_flexy_payment_columns_to_orders_table.php`

### Critical Points

1. **Duplicate Prevention:** DigiflazzService checks for existing statuses before submitting
2. **Multi-Quantity Support:** System tracks multiple top-ups per order (e.g., 3x Weekly Pass)
3. **Order Matching:** Webhook uses multiple strategies (ref_id parsing, customer_no matching)
4. **Status Synchronization:** VipResellerStatus mirrors DigiflazzStatus for backward compatibility
5. **Manual Approval:** Flexy payments require admin approval before Digiflazz submission
6. **Telegram Integration:** Status changes trigger Telegram notifications

---

## Appendix: Status Flow Diagrams

### Digiflazz Flow (Chargily Payment)
```
Order Created (pending_bmccp)
    ↓
Chargily Payment Success
    ↓
DigiflazzService::placeOrder()
    ↓
DigiflazzStatus created (event: 'create')
    ↓
Order status → sending
    ↓
[Digiflazz processes top-up]
    ↓
Webhook received (status: 'sukses')
    ↓
DigiflazzStatus updated
    ↓
Order status → completed
```

### Flexy Flow
```
Order Created (pending_flexy)
    ↓
User uploads receipt
    ↓
Flexy record created
Order status → pending_confirmation
    ↓
Admin approves (via dashboard/Telegram)
    ↓
Order status → completed (trigger)
    ↓
DigiflazzService::placeOrder() called
    ↓
Order status → sending
    ↓
Webhook updates
    ↓
Order status → completed (final)
```

---

*Last Updated: Based on codebase analysis*
*Version: 1.0*
