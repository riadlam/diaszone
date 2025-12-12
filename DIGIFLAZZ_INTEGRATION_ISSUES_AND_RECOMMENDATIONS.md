# Digiflazz Integration Analysis - Issues and Recommendations

## Executive Summary
After analyzing the codebase against Digiflazz API documentation, several potential issues were identified that could lead to order conflicts, especially with multi-quantity orders and concurrent transactions.

---

## Critical Issues

### 1. ⚠️ Race Condition in Order Matching by customer_no

**Problem:**
When webhooks arrive and the `ref_id` doesn't match any existing `DigiflazzStatus` record (edge case), the system falls back to matching by `customer_no`. The `findOrderByCustomerNo()` method uses `latest()->first()`, which can match the wrong order if:
- Multiple orders exist for the same customer (user makes multiple orders quickly)
- Webhooks arrive out of order
- Initial `DigiflazzStatus` record isn't created yet when webhook arrives

**Location:**
- `app/Http/Controllers/Webhook/DigiflazzWebhookController.php::findOrderByCustomerNo()`

**Current Code:**
```php
// Line 245: Mobile Legends user.zone pattern
$order = Order::where('user_id_ml', $userId)
    ->where('zone_id_ml', $zone)
    ->latest()  // ← Gets most recent, could be wrong order
    ->first();

// Line 285: Concatenated user+zone (fallback)
$order = Order::whereRaw("CONCAT(user_id_ml, zone_id_ml) = ?", [$customerNo])
    ->latest()  // ← Same issue
    ->first();
```

**Recommendation:**
Prioritize orders that are actively being processed and not yet completed. The code already attempts this with `statusPriority`, but the fallback still uses `latest()`. Additionally, prefer matching by `ref_id` which contains order ID.

**Fix:**
```php
// In findOrderByCustomerNo(), after statusPriority matching fails:
// Instead of latest()->first(), prioritize by:
// 1. Status (sending/pending over completed)
// 2. Created_at (newer is better)
// 3. Match by ref_id parsing if possible
```

---

### 2. ⚠️ Multi-Quantity Order: Potential Duplicate Detection Issue

**Problem:**
For multi-quantity orders (e.g., quantity=3), the code calls `placeOrder()` in a loop. Each call generates a unique `ref_id` using `Str::random(8)`. However, the duplicate check in `DigiflazzService::placeOrder()` only counts existing `DigiflazzStatus` records, not the in-flight API calls.

**Location:**
- `app/Services/DigiflazzService.php::placeOrder()` (line 38-47)
- `app/Http/Controllers/CheckoutController.php` (line 1449-1453)

**Current Code:**
```php
// DigiflazzService.php - Duplicate check
$existingCount = DigiflazzStatus::where('order_id', $order->id)
    ->where(function ($q) {
        $q->whereIn('status', ['Sukses', 'sukses', 'SUCCESS', 'success', 'waiting', 'pending'])
          ->orWhere('event', 'create');
    })->count();

$target = isset($order->quantity) ? (int)$order->quantity : 1;
if ($existingCount >= $target) {
    return ['result' => false, 'message' => 'Order already submitted to Digiflazz'];
}
```

**Issue:**
If `placeOrder()` is called multiple times rapidly (e.g., in a loop), the duplicate check happens before the first API call completes and creates the `DigiflazzStatus` record. This means:
- First call: `existingCount = 0`, proceeds
- Second call (before first completes): `existingCount = 0`, proceeds (should be 1)
- Third call (before first two complete): `existingCount = 0`, proceeds (should be 2)

This could lead to more API calls than `order->quantity`.

**Recommendation:**
Use database locking or a more atomic check. Consider using `DB::transaction()` with row locking or a separate counter/flag.

**Alternative Fix:**
Count pending statuses including those with `event = 'create'` immediately after API call, before loop continues.

---

### 3. ⚠️ Digiflazz API Recommendation Not Fully Implemented

**Digiflazz Documentation States:**
> "Untuk transaksi dengan status pending, Anda dapat melakukan cek status dengan melakukan topup ulang dengan ref_id yang sama pada transaksi sebelumnya."

**Translation:** "For transactions with pending status, you can check status by resubmitting top-up with the same ref_id as the previous transaction."

**Current Implementation:**
- Every call to `placeOrder()` generates a new `ref_id`: `'order-' . $order->id . '-' . Str::random(8)`
- For status checking/retry of pending transactions, we should reuse the same `ref_id`

**Impact:**
- Not critical for multi-quantity orders (each top-up needs unique ref_id)
- Could be an issue if we want to implement automatic status checking for pending transactions
- May cause confusion if Digiflazz sees duplicate ref_ids (though they should be unique per transaction)

**Recommendation:**
For multi-quantity orders, current approach is correct (unique ref_id per top-up). For retry/status-check scenarios, implement a method that reuses the original `ref_id` from the first `DigiflazzStatus` record.

---

### 4. ⚠️ Webhook Payload Extraction Edge Case

**Current Code:**
```php
// DigiflazzWebhookController.php line 20
$payload = $request->json('data') ?? $request->json()->all();
```

**Digiflazz Documentation:**
Shows payload is always wrapped in `data` key:
```json
{
    "data": {
        "ref_id": "30467470",
        "customer_no": "081280556115",
        ...
    }
}
```

**Issue:**
The fallback `$request->json()->all()` might work, but if Digiflazz sends payload directly (not wrapped), we'd be processing the wrong structure. The current implementation handles both cases, but we should be explicit.

**Recommendation:**
Always expect `data` wrapper per Digiflazz docs. If `$request->json('data')` is null, log a warning.

---

### 5. ⚠️ customer_no Matching for Multi-Quantity Orders

**Scenario:**
User has order #123 with quantity=3:
- All 3 top-ups use same `customer_no` (e.g., "2057629734048")
- Each has unique `ref_id` (e.g., "order-123-abc123", "order-123-def456", "order-123-ghi789")
- Webhook arrives for "order-123-abc123" but `DigiflazzStatus` not created yet

**Current Behavior:**
Webhook falls back to `findOrderByCustomerNo("2057629734048")`, which should match order #123 correctly.

**Potential Issue:**
If user makes another order #124 with same customer_no before webhooks for order #123 arrive:
- Order #124: customer_no = "2057629734048", status = "sending"
- Order #123 webhook arrives, matches order #124 (most recent) → **WRONG ORDER**

**Mitigation:**
The code already prioritizes orders by status (`statusPriority = ['sending', 'pending', ...]`), which helps but doesn't guarantee correctness if both orders are in same status.

**Recommendation:**
Always prefer `ref_id` parsing over `customer_no` matching. If `ref_id` contains order ID pattern (`order-123-...`), use that instead of customer_no fallback.

---

## Medium Priority Issues

### 6. Missing User-Agent Header Check

**Digiflazz Documentation:**
Webhooks send different `User-Agent` headers:
- `Digiflazz-Hookshot` for prepaid transactions
- `Digiflazz-Pasca-Hookshot` for postpaid transactions

**Current Implementation:**
No check for `User-Agent` header. The webhook handler processes all webhooks the same way.

**Recommendation:**
Add User-Agent validation to ensure we're processing the correct transaction type. Log if unexpected User-Agent is received.

---

### 7. Webhook Event Type Handling

**Digiflazz Documentation:**
Webhooks send `X-Digiflazz-Event` header with values:
- `create` - New transaction created
- `update` - Transaction status updated

**Current Implementation:**
Event is stored in `DigiflazzStatus.event` but not used for different logic paths.

**Recommendation:**
Consider handling `create` and `update` events differently:
- `create` events might need initial order linking
- `update` events might need status validation/confirmation

---

## Low Priority / Observations

### 8. Database Constraints

**Current State:**
- `digiflazz_statuses.ref_id` has unique index ✓
- `digiflazz_statuses.trxid` has unique index ✓
- `orders.id` is primary key ✓

**Good:** Unique constraints prevent duplicate status records.

### 9. Transaction Locking

**Current Implementation:**
Uses `lockForUpdate()` when attaching status to order, which prevents race conditions. ✓

**Good:** Proper use of database locking.

---

## Recommendations Summary

### ✅ COMPLETED ACTIONS:

1. **✅ Improved Order Matching Logic**
   - **Primary method:** Always parse order ID from ref_id first (format: "order-{order_id}-{random}")
   - **Fallback:** customer_no matching only used if ref_id doesn't contain order ID pattern
   - Added time window check (only matches orders created within last 24 hours)
   - Added status priority filtering (prefers orders in active statuses)
   - Added warnings when multiple candidates match

2. **✅ Fixed Multi-Quantity Loop Race Condition**
   - Implemented atomic DB transaction with `lockForUpdate()` in CheckoutController and AdminController
   - Duplicate count re-checked inside transaction before each loop iteration
   - Added small delay between API calls to ensure DigiflazzStatus records are committed

3. **✅ Added User-Agent Validation**
   - Validates webhook User-Agent header (Digiflazz-Hookshot, Digiflazz-Pasca-Hookshot)
   - Logs warnings for unexpected values (but continues processing)

4. **✅ Simplified Webhook Handler**
   - Removed redundant code and duplicate logic paths
   - Single transaction block handles all status updates
   - Clean ref_id-first approach with proper fallback

### Medium-Term Improvements:

4. **Implement Status Check Retry Logic**
   - For pending transactions, implement retry using same `ref_id`
   - Add scheduled job to check pending statuses older than X minutes

5. **Enhanced Logging**
   - Log when customer_no matching finds multiple candidates
   - Log when webhook ref_id doesn't match any existing record
   - Log when order matching seems ambiguous

6. **Add Webhook Validation Tests**
   - Test multi-quantity order webhook handling
   - Test concurrent order scenarios
   - Test webhook arrival before initial status record creation

---

## Code Snippets for Fixes

### Fix 1: Improved customer_no Matching

```php
protected function findOrderByCustomerNo($customerNo, $refId = null)
{
    // First, try to extract order ID from ref_id if available
    if ($refId && preg_match('/order-(\d+)/', $refId, $m)) {
        $orderId = (int)$m[1];
        $order = Order::find($orderId);
        if ($order) {
            Log::info('Digiflazz webhook: matched by ref_id pattern in customer_no fallback', [
                'customer_no' => $customerNo,
                'ref_id' => $refId,
                'order_id' => $order->id
            ]);
            return $order;
        }
    }

    // Rest of existing logic...
    // Add time window check: only match orders created in last 24 hours
    $timeWindow = now()->subHours(24);
    
    // Mobile Legends concatenated pattern
    $candidates = Order::whereRaw("CONCAT(user_id_ml, zone_id_ml) = ?", [$customerNo])
        ->whereIn('status', $statusPriority)
        ->where('created_at', '>=', $timeWindow) // Add time window
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($candidates->count() > 1) {
        Log::warning('Digiflazz webhook: multiple orders match customer_no', [
            'customer_no' => $customerNo,
            'count' => $candidates->count(),
            'order_ids' => $candidates->pluck('id')->toArray()
        ]);
    }
    
    return $candidates->first();
}
```

### Fix 2: Atomic Multi-Quantity Submission

```php
// In CheckoutController or wherever loop happens:
DB::transaction(function () use ($order, $required, $submitted) {
    // Lock the order to prevent concurrent modifications
    $orderLocked = Order::where('id', $order->id)->lockForUpdate()->first();
    
    // Re-check existing count inside transaction
    $currentCount = $orderLocked->digiflazzStatuses()
        ->where(function ($q) {
            $q->whereIn('status', ['Sukses', 'sukses', 'SUCCESS', 'success', 'waiting', 'pending'])
              ->orWhere('event', 'create');
        })->count();
    
    $remaining = max(0, $required - $currentCount);
    
    for ($i = 0; $i < $remaining; $i++) {
        $result = app(DigiflazzService::class)->placeOrder($order->diamondPack, $orderLocked);
        // Status record should be created immediately, so next iteration sees updated count
    }
});
```

### Fix 3: User-Agent Validation

```php
public function handle(Request $request)
{
    $userAgent = $request->header('User-Agent');
    $expectedAgents = ['Digiflazz-Hookshot', 'Digiflazz-Pasca-Hookshot'];
    
    if ($userAgent && !in_array($userAgent, $expectedAgents)) {
        Log::warning('Digiflazz webhook: unexpected User-Agent', [
            'user_agent' => $userAgent,
            'expected' => $expectedAgents
        ]);
        // Continue processing but log warning
    }
    
    // Rest of handler...
}
```

---

## Testing Recommendations

1. **Multi-Quantity Order Test**
   - Create order with quantity=3
   - Verify exactly 3 DigiflazzStatus records created
   - Send 3 webhooks, verify order completes only after all 3 succeed

2. **Concurrent Orders Test**
   - Create 2 orders for same customer_no rapidly
   - Send webhooks for both orders
   - Verify each webhook matches correct order

3. **Webhook Timing Test**
   - Send webhook before initial DigiflazzStatus record created
   - Verify order matching still works correctly

4. **Race Condition Test**
   - Simulate multiple placeOrder() calls simultaneously
   - Verify no duplicate submissions occur

---

*Last Updated: Based on codebase analysis and Digiflazz API documentation*
*Version: 1.0*
