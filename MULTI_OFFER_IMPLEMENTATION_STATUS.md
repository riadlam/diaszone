# Multi-Offer Ordering Implementation - Status

## ✅ Completed

1. **Database Migrations**
   - ✅ `order_items` table created
   - ✅ `digiflazz_statuses` table updated (added `diamond_pack_id`, `order_item_id`)

2. **Models**
   - ✅ `OrderItem` model created
   - ✅ `Order` model updated (added `orderItems()` relationship, helper methods)
   - ✅ `DigiflazzStatus` model updated (added relationships)

3. **Order Creation Logic**
   - ✅ Validation updated (allows multiple items, same game validation)
   - ✅ Single order with multiple order_items implementation started

## ⏳ In Progress / TODO

### 4. CheckoutController Cleanup
- [ ] Remove duplicate old code (the foreach loop that still exists)
- [ ] Test order creation with multiple items
- [ ] Handle payment method assignment properly

### 5. Top-Up Submission Logic
**File:** `CheckoutController.php` (around line 1439)
**Need to update:**
```php
// OLD: Loop through order.quantity
// NEW: Loop through order.orderItems, then through each item's quantity

DB::transaction(function () use (&$result, &$order) {
    $orderLocked = Order::where('id', $order->id)->lockForUpdate()->first();
    
    foreach ($orderLocked->orderItems as $orderItem) {
        // Check how many DigiflazzStatus records already exist for this item
        $submitted = $orderItem->digiflazzStatuses()
            ->where(function ($q) {
                $q->whereIn('status', ['Sukses', 'sukses', 'SUCCESS', 'success', 'waiting', 'pending'])
                  ->orWhere('event', 'create');
            })->count();
        
        $remaining = max(0, $orderItem->quantity - $submitted);
        
        for ($i = 0; $i < $remaining; $i++) {
            // Generate unique ref_id: "order-{order_id}-item-{order_item_id}-{random8}"
            $refId = 'order-' . $order->id . '-item-' . $orderItem->id . '-' . Str::random(8);
            
            $result = app(DigiflazzService::class)->placeOrderWithRefId(
                $orderItem->diamondPack,
                $orderLocked,
                $refId,
                $orderItem->id
            );
            
            // Create DigiflazzStatus immediately
            DigiflazzStatus::create([
                'order_id' => $orderLocked->id,
                'order_item_id' => $orderItem->id,
                'diamond_pack_id' => $orderItem->diamond_pack_id,
                'ref_id' => $refId,
                'buyer_sku_code' => $orderItem->diamondPack->code,
                'event' => 'create',
                // ... other fields from API response
            ]);
            
            usleep(100000); // 0.1s delay
        }
    }
});
```

### 6. DigiflazzService Update
**File:** `app/Services/DigiflazzService.php`
**Need to add:**
```php
public function placeOrderWithRefId($pack, $order, $refId, $orderItemId = null): array
{
    // Similar to placeOrder(), but accepts custom refId
    // Also accept orderItemId to store in DigiflazzStatus
}
```

### 7. Webhook Handler
**File:** `app/Http/Controllers/Webhook/DigiflazzWebhookController.php`
**Update:**
- Parse ref_id format: `"order-{order_id}-item-{order_item_id}-{random}"`
- Link to correct `order_item_id`
- Check completion per order_item, then per order

### 8. Order Status Updates
**File:** `app/Http/Controllers/Webhook/DigiflazzWebhookController.php::applyStatusToOrder()`
**Update:**
```php
// Check if all order_items are complete
$allComplete = true;
foreach ($order->orderItems as $item) {
    if (!$item->isCompleted()) {
        $allComplete = false;
        break;
    }
}

if ($allComplete) {
    $order->update(['status' => 'completed']);
} else {
    $order->update(['status' => 'sending']);
}
```

### 9. Telegram Messages
**File:** `app/Services/TelegramService.php::formatOrderMessage()`
**Update to show:**
- List all packs with quantities
- Progress per pack (2/4 completed)
- Overall progress (3/6 completed)

### 10. UI Components
**Files:**
- `resources/views/components/diamond-packs.blade.php`
- `resources/views/components/mobile-bottom-sheet.blade.php`
- `resources/js/app.js`

**Add:**
- Quantity selector (+/- buttons) next to each pack
- Cart-like selection (multiple packs can be selected)
- Update total price calculation
- Submit all selected packs in single request

---

## Testing Checklist

- [ ] Create order with 1 pack
- [ ] Create order with multiple packs (same game)
- [ ] Reject order with packs from different games
- [ ] Submit top-ups for multi-item order
- [ ] Receive webhooks and update individual statuses
- [ ] Check order completion when all items done
- [ ] Verify telegram message shows progress
- [ ] Test UI quantity selectors

---

## Next Steps

1. **Immediate:** Complete CheckoutController order creation (remove old code)
2. **Next:** Update top-up submission logic
3. **Then:** Update webhook handler
4. **Finally:** UI implementation

---

*Last Updated: Implementation in progress*
