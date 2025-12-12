# DiasZone Website - Deep Analysis Report

## Executive Summary

This Laravel-based gaming top-up platform supports 5 games (Mobile Legends, Free Fire, PUBG Mobile, Honor of Kings, Blood Strike) with three payment methods (Flexy, Baridimob/Chargily, Cryptocurrency). The site uses a responsive design with distinct mobile and desktop experiences.

---

## 1. OFFER LOADING SYSTEM

### 1.1 Database Layer
**Location:** `app/Models/DiamondPack.php`

Packs are stored in the `diamond_packs` table with:
- `game_type`: mobilelegends, freefire, pubgmobile, honorofkings, bloodstrike
- `is_active`: boolean flag for availability
- `sort_order`: primary sort key
- `price`, `price_usd`, `price_dzd`: multi-currency support
- `discount_percentage`: discount calculation
- `special_quantity`: for multi-quantity offers (e.g., 3× Weekly Pass)
- `diamonds`, `bonus_diamonds`: quantity fields

### 1.2 Backend Loading Logic
**Location:** `app/Http/Controllers/HomeController.php` → `gameTopUp()`

```php
$packs = DiamondPack::where('game_type', $gameType)
    ->where('is_active', true)
    ->orderBy('sort_order')      // Primary sort
    ->orderBy('price')            // Secondary sort
    ->get();
```

**Key Points:**
- Packs filtered by game type and active status
- Dual sorting ensures consistent display order
- All packs loaded upfront (no pagination on main pages)
- Passed to view as `$packs` collection

### 1.3 Frontend Display (Desktop vs Mobile)

#### Desktop View (`components/diamond-packs.blade.php`)
- **Layout:** 2-column grid (`grid-cols-1 md:grid-cols-2`)
- **Visibility:** `hidden lg:block` (shown only on ≥1024px screens)
- **Structure:** Radio buttons with styled labels, first pack checked by default
- **Positioning:** Left column, scrollable, fixed width
- **Selection:** Direct click on pack cards, visual feedback via border color changes

#### Mobile View (`components/mobile-bottom-sheet.blade.php`)
- **Trigger:** Button "Select Top-Up Amount" (visible only on mobile via `lg:hidden`)
- **Component:** Bottom sheet modal sliding up from bottom (85vh height)
- **Behavior:** 
  - Overlay with backdrop
  - Scrollable pack list
  - Touch-friendly interface
  - Closes after selection (200ms delay)
- **JavaScript:** `diamond-packs.blade.php` lines 340-724 handles all bottom sheet logic

**Critical Mobile Logic:**
```javascript
// Detection: Checks if mobile button is visible
function isMobile() {
    const mobileBtn = document.getElementById('mobile-select-pack-btn');
    if (!mobileBtn) return false;
    const styles = window.getComputedStyle(mobileBtn);
    return styles.display !== 'none';
}
```

---

## 2. PAYMENT SYSTEM ARCHITECTURE

### 2.1 Payment Flow Overview

```
User selects pack → Fills order form → Clicks "Buy Now"
    ↓
Order created via /api/orders/create (status: pending_*)
    ↓
Redirect to /select (payment method selection)
    ↓
User selects payment method → Redirects to payment form
    ↓
Payment processing (varies by method)
    ↓
Webhook/callback updates order status
    ↓
Order processed → Diamonds sent to player
```

### 2.2 Order Creation (`CheckoutController::createOrder()`)

**Location:** `app/Http/Controllers/CheckoutController.php` lines 197-421

**Process:**
1. **Validation:**
   - Single item limit enforced (`max:1` in cart_items)
   - Game-specific field validation (user_id/zone_id for ML, player_id for FF/PUBG/HOK, user_id+server for BS)
   - Rate limiting: 20 orders/minute per IP/user

2. **Order Status Assignment:**
   ```php
   if ($paymentMethod === 'flexy') {
       $orderStatus = 'pending_flexy';
   } elseif ($paymentMethod === 'bmccp') {
       $orderStatus = 'pending_bmccp';
   } elseif ($paymentMethod === 'cryptocurrency') {
       $orderStatus = 'pending_cryptopay';
   }
   ```

3. **Price Calculation:**
   - Uses `special_quantity` if present (e.g., 3× Weekly Pass)
   - Calculates discount: `totalDiscount = (price_dzd * discount_percentage / 100) * quantity`
   - Stores `original_price` and `final_price` on order record

4. **Order Storage:**
   - Encrypted order ID returned to frontend
   - Stored in localStorage as `diaszone_encrypted_order_id`
   - Telegram notification sent (skipped for `pending_flexy`)

### 2.3 Payment Methods

#### A. Flexy Payment
**Routes:** 
- GET `/select/flexy?order_id={encrypted}` → Shows upload form
- POST `/select/flexy` → Handles receipt upload

**Process:**
1. User uploads payment receipt (image/PDF, max 10MB)
2. File stored in `storage/app/public/flexy_receipts/`
3. `Flexy` record created with `status='pending'`
4. Order status remains `pending_flexy`
5. Admin manually approves/rejects via `/adm/flexy-approvals`
6. On approval: Order processed → Status → `sending` → `completed`

**Key Files:**
- View: `resources/views/pages/flexy-form.blade.php`
- Controller: `CheckoutController::submitFlexy()` (line ~2256)

#### B. Baridimob (Chargily Pay v2)
**Routes:**
- GET `/select/bmccp/{encrypted_order_id}` → Shows payment page
- POST `/api/baridimob/process` → Initiates Chargily payment
- POST `/webhook/baridimob` → Receives payment confirmation

**Process:**
1. Frontend calls `/api/baridimob/process` with encrypted order ID
2. Backend creates Chargily Pay invoice via `ChargilyPayV2Service`
3. Returns payment URL to frontend
4. User redirected to Chargily checkout page
5. After payment: Chargily webhook → Updates order status to `pending_bmccp` → Auto-processed

**Key Files:**
- Service: `app/Services/ChargilyPayV2Service.php`
- Controller: `CheckoutController::processBaridimobPayment()` (line 628)

**Price Calculation:**
```php
$unitPriceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
$quantity = $order->quantity ?? 1;
$totalBeforeDiscount = $unitPriceDzd * $quantity;
$discount = ($unitPriceDzd * $discountPercentage / 100) * $quantity;
$final = $totalBeforeDiscount - $discount;
```

#### C. Cryptocurrency Payment
**Routes:**
- GET `/select/crypto/{encrypted_order_id}` → Shows crypto form
- POST `/api/orders/check-crypto-payment` → Polls payment status
- POST `/webhook/nowpayments` or `/webhook/mixpay` → Receives confirmation

**Providers:** NOWPayments and MixPay

**Process:**
1. User selects cryptocurrency
2. Backend creates payment via `NowPaymentsService` or `MixPayService`
3. Returns payment address/QRCode to frontend
4. Frontend polls `/api/orders/check-crypto-payment` every 5 seconds
5. Webhook confirms payment → Order processed

**Key Files:**
- Services: `app/Services/NowPaymentsService.php`, `app/Services/MixPayService.php`
- Controller: `CheckoutController::cryptoForm()` (line 2255)

---

## 3. MOBILE DROPDOWN/BOTTOM SHEET MECHANICS

### 3.1 Component Structure

**File:** `resources/views/components/mobile-bottom-sheet.blade.php`

**Elements:**
1. **Bottom Sheet Container:** 
   - Fixed position, bottom-aligned
   - 85vh max height
   - Rounded top corners (24px)
   - Z-index: 9999

2. **Header:**
   - Title: "Select Top-Up Amount"
   - Close button (X icon)

3. **Scrollable Content:**
   - Pack list with images, names, prices
   - Each pack has data attributes: `data-pack-id`, `data-pack-price-dzd`, etc.

4. **Overlay:**
   - Dark backdrop (50% opacity)
   - Closes sheet on click

### 3.2 JavaScript Behavior (`diamond-packs.blade.php` lines 340-724)

**Initialization:**
- Checks if mobile (button visible)
- Waits for DOM ready
- Multiple retry attempts (200ms, 500ms, 1000ms delays) for late-loading elements

**Open Logic:**
```javascript
function openBottomSheet() {
    // Show overlay
    bottomSheetOverlay.style.display = 'block';
    // Show bottom sheet
    bottomSheet.style.display = 'flex';
    // Force transform via requestAnimationFrame
    requestAnimationFrame(() => {
        bottomSheet.style.setProperty('transform', 'translateY(0)', 'important');
    });
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}
```

**Pack Selection:**
1. User clicks pack item
2. Visual feedback: Border color changes, checkmark appears
3. Updates mobile button text with selected pack info
4. Syncs with desktop: Finds corresponding radio button, checks it, dispatches `change` event
5. Closes bottom sheet after 200ms

**Close Logic:**
- Transform: `translateY(100%)`
- Hide after 300ms (matches CSS transition)
- Restore body scroll

### 3.3 Mobile vs Desktop Synchronization

**Critical Code (line 688):**
```javascript
// Update the hidden radio button (for form submission and desktop JS compatibility)
const radio = document.querySelector(`input[name="diamond_pack"][value="${packId}"]`);
if (radio) {
    radio.checked = true;
    // Trigger change event to update order form via existing app.js
    const changeEvent = new Event('change', { bubbles: true });
    radio.dispatchEvent(changeEvent);
}
```

This ensures:
- Order form updates correctly
- Price calculations run
- Form submission includes correct pack ID

---

## 4. DESKTOP VIEW DETAILS

### 4.1 Layout Structure (`pages/game-topup.blade.php`)

```
┌─────────────────────────────────────────┐
│  Game Header (all screen sizes)        │
└─────────────────────────────────────────┘
┌─────────────────┬──────────────────────┐
│  Left Column    │  Right Column        │
│  (Desktop only) │  (All sizes)         │
│                 │                      │
│  Diamond Packs  │  Order Form          │
│  Grid (2 cols)  │  (Sticky on desktop)│
│                 │                      │
│  Scrollable     │  - User ID/Player ID│
│                 │  - Zone ID/Server    │
│                 │  - Total Price       │
│                 │  - Buy Now Button    │
└─────────────────┴──────────────────────┘
```

### 4.2 Pack Display Logic

**Template:** `components/diamond-packs.blade.php` lines 7-175

**Each Pack Card Contains:**
- Radio input (hidden, used for form submission)
- Image (game-specific, conditional logic for different games)
- Pack name with quantity handling
- Bonus diamonds indicator
- Price with discount calculation
- Hover effects (border color change)

**Price Display:**
- Original price (strikethrough if discount)
- Final price (after discount)
- Currency-aware (DZD/USD via `updatePricesOnPage()` function)

**Image Selection Logic:**
- Mobile Legends: Based on diamond quantity (diaslow.webp → diasmid.webp → diaslarge.webp → diasbigbig.webp)
- Free Fire: Similar tiered system (diamondssmallfreefire.webp → ... → freefirelaaargediamonds.webp)
- Honor of Kings: Token-based images from `honorofkings/` folder
- PUBG Mobile/Blood Strike: No images (empty divs for layout consistency)

### 4.3 Currency Switching

**Function:** `updatePricesOnPage()` (lines 179-297)

**Behavior:**
- Listens to `currencyChanged` event
- Updates all `.pack-final-price` and `.pack-original-price` elements
- Calculates based on `data-price-usd` and `data-price-dzd` attributes
- Formats: DZD (rounded, no decimals) vs USD (2 decimals)

**Storage:** `localStorage.getItem('diaszone_currency')` or `CurrencyManager.getCurrency()`

---

## 5. KEY FINDINGS & OBSERVATIONS

### 5.1 Strengths
1. **Separation of Concerns:** Clear split between desktop/mobile views
2. **Multi-currency Support:** Well-implemented DZD/USD switching
3. **Security:** Encrypted order IDs, rate limiting, input validation
4. **Responsive Design:** Proper mobile-first approach with bottom sheet
5. **Payment Flexibility:** Three distinct payment methods with different flows

### 5.2 Potential Issues

#### Mobile Bottom Sheet
- **Initialization Race Condition:** Multiple retry delays suggest potential timing issues
- **Heavy JavaScript:** 384 lines of JS in blade template could be extracted
- **Transform Overrides:** Multiple `!important` flags indicate CSS specificity conflicts

#### Payment Processing
- **Flexy Manual Approval:** No automation (by design, but could bottleneck)
- **Webhook Reliability:** Depends on external services (Chargily, NOWPayments, MixPay)
- **Price Calculation:** Multiple places calculate prices (potential inconsistency risk)

#### Offer Loading
- **No Pagination:** All packs loaded at once (could be slow with many packs)
- **No Caching:** Database query runs on every page load
- **Sort Order Dependency:** Relies on `sort_order` column (must be maintained manually)

### 5.3 Recommended Improvements

1. **Extract JavaScript:**
   - Move bottom sheet logic to `resources/js/bottom-sheet.js`
   - Use Laravel Mix/Vite for bundling

2. **Add Caching:**
   ```php
   $packs = Cache::remember("packs_{$gameType}", 3600, function() use ($gameType) {
       return DiamondPack::where('game_type', $gameType)
           ->where('is_active', true)
           ->orderBy('sort_order')
           ->orderBy('price')
           ->get();
   });
   ```

3. **Price Calculation Service:**
   - Create `PackPriceCalculator` service
   - Centralize all price logic (discount, quantity, currency conversion)

4. **Mobile Performance:**
   - Lazy load bottom sheet content
   - Debounce scroll events
   - Optimize image loading

5. **Payment Status Tracking:**
   - Add `payment_status` table for audit trail
   - Store payment attempt history
   - Better error handling/recovery

---

## 6. FILE STRUCTURE REFERENCE

### Core Payment Files
- `app/Http/Controllers/CheckoutController.php` - Payment processing
- `app/Services/ChargilyPayV2Service.php` - Baridimob integration
- `app/Services/NowPaymentsService.php` - Crypto payment (NOWPayments)
- `app/Services/MixPayService.php` - Crypto payment (MixPay)

### Frontend Components
- `resources/views/components/diamond-packs.blade.php` - Desktop pack grid + mobile JS
- `resources/views/components/mobile-bottom-sheet.blade.php` - Mobile pack selector
- `resources/views/components/order-form.blade.php` - Order input form
- `resources/views/pages/game-topup.blade.php` - Main game page layout

### Routes
- `routes/web.php` - All route definitions (lines 16-280)

---

## 7. PAYMENT FLOW DIAGRAMS

### Flexy Flow
```
User → Select Pack → Create Order (pending_flexy)
  → Upload Receipt → Admin Reviews
  → Admin Approves → Order Processed → Completed
```

### Baridimob Flow
```
User → Select Pack → Create Order (pending_bmccp)
  → Redirect to Chargily → User Pays
  → Chargily Webhook → Order Auto-Processed → Completed
```

### Cryptocurrency Flow
```
User → Select Pack → Create Order (pending_cryptopay)
  → Get Payment Address → User Sends Crypto
  → Poll Status / Webhook → Payment Confirmed
  → Order Processed → Completed
```

---

**Analysis Date:** Generated automatically  
**Codebase Version:** Current state of repository  
**Focus Areas:** Payment logic, offer loading, mobile/desktop views
