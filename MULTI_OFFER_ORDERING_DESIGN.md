# Multi-Offer Ordering System - Design Document

## Overview
Allow users to order multiple different diamond packs from the same game in a single order, with quantity selectors for each pack.

**Example:** Order 4x Pack ID 172 + 2x Pack ID 175 (Mobile Legends)

---

## Database Changes

### 1. `order_items` Table (NEW)
Stores multiple packs per order:
- `id` (primary key)
- `order_id` (foreign key to orders)
- `diamond_pack_id` (foreign key to diamond_packs)
- `quantity` (how many of this pack)
- `unit_price_dzd`, `unit_price_usd` (snapshot prices)
- `discount_percentage`
- `subtotal_dzd`, `discount_amount_dzd`, `total_dzd`

### 2. `digiflazz_statuses` Table (MODIFIED)
Add:
- `diamond_pack_id` (which pack this status belongs to)
- `order_item_id` (which order_item this status belongs to)

**Purpose:** Track status per top-up. If order has 4x pack 172, we create 4 DigiflazzStatus records, each with unique `ref_id`.

---

## Order Flow

### Step 1: User Selection (UI)
- Desktop: Quantity selector (+/- buttons) next to each pack
- Mobile: Quantity selector in bottom sheet
- Cart-like selection: Multiple packs can have quantity > 0
- Validation: All packs must be from same game

### Step 2: Order Creation
**Endpoint:** `POST /api/orders/create`

**Request:**
```json
{
  "cart_items": [
    {"pack_id": 172, "quantity": 4},
    {"pack_id": 175, "quantity": 2}
  ],
  "user_id": "123",
  "zone_id": "456",
  "payment_method": "chargily"
}
```

**Validation:**
1. All packs exist
2. All packs have same `game_type`
3. Quantities > 0
4. User/player IDs valid for game type

**Create:**
1. One `Order` record
2. Multiple `OrderItem` records (one per pack)
3. Calculate `final_price` = sum of all item totals

### Step 3: Payment
Same as before - wait for Chargily/Flexy/etc. confirmation.

### Step 4: Top-Up Submission
When payment succeeds:

**For each OrderItem:**
1. Loop `quantity` times
2. Each iteration calls `placeOrder()` with:
   - `order` (the main order)
   - `pack` (from order_item)
   - Generates unique `ref_id`: `"order-{order_id}-item-{order_item_id}-{random8}"`
3. Creates `DigiflazzStatus` with:
   - `order_id`
   - `order_item_id`
   - `diamond_pack_id`
   - `ref_id`
   - `buyer_sku_code` (from pack)

**Example:**
- Order ID 123
- Item 1: Pack 172, quantity 4
- Item 2: Pack 175, quantity 2
- Creates 6 DigiflazzStatus records:
  - `order-123-item-1-abc123`, pack 172
  - `order-123-item-1-def456`, pack 172
  - `order-123-item-1-ghi789`, pack 172
  - `order-123-item-1-jkl012`, pack 172
  - `order-123-item-2-mno345`, pack 175
  - `order-123-item-2-pqr678`, pack 175

### Step 5: Webhook Processing
Webhook arrives with `ref_id`.

**Matching:**
1. Parse `ref_id`: `"order-{order_id}-item-{order_item_id}-{random}"`
2. Find `DigiflazzStatus` by `ref_id` (or `trxid`)
3. Update status record
4. Check if all statuses for that `order_item` are complete
5. Check if all order_items for that `order` are complete
6. If all complete, set order status to `completed`

### Step 6: Telegram Updates
Rich message showing:
```
🆕 New Order Created

📦 Order: ORD-ABC123
🎮 Game: Mobile Legends
💎 Packs:
   • 4x Weekly Diamond Pass (172)
   • 2x 1000 Diamonds (175)
💰 Total: 15,000 DZD
📊 Status: Sending

🔁 Top-ups Progress: 3/6 completed
   • Pack 172: 2/4 ✅✅⏳⏳
   • Pack 175: 1/2 ✅⏳

👤 User: John Doe
🆔 User ID: 123
🌍 Zone ID: 456
```

Update message when each webhook arrives.

---

## UI Components

### Desktop Pack Card
```
┌─────────────────────────────┐
│ [Image] 1000 Diamonds    [ ]│
│         500 DZD         Qty:│
│                      [-][1][+]│
└─────────────────────────────┘
```

### Mobile Bottom Sheet
Similar, but with quantity selector inline.

---

## Security Measures

1. **Server-side validation:**
   - Pack existence
   - Same game type
   - Quantity limits (max 10 per pack? configurable)
   - User ID format validation

2. **Rate limiting:**
   - Existing rate limits apply
   - Consider per-user order creation limits

3. **SQL injection:**
   - Use Eloquent/parameterized queries

4. **Race conditions:**
   - Use DB transactions for order creation
   - Use `lockForUpdate()` for top-up submission

---

## Implementation Priority

1. ✅ Database migrations
2. ✅ Models (OrderItem, update Order, DigiflazzStatus)
3. ⏳ UI: Quantity selectors
4. ⏳ JS: Multi-pack cart logic
5. ⏳ CheckoutController: Validation & order_items creation
6. ⏳ Top-up: Create multiple DigiflazzStatus records
7. ⏳ Webhook: Update individual statuses
8. ⏳ Telegram: Rich progress messages
9. ⏳ Order status: Check completion

---

*Version 1.0 - Design Phase*
