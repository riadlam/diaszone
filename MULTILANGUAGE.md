# Multi-Language Support

This project now supports multiple languages: **English (EN)**, **French (FR)**, and **Arabic (AR)**.

## How It Works

1. **Language Files**: Located in `resources/lang/`
   - `en.json` - English translations
   - `fr.json` - French translations
   - `ar.json` - Arabic translations

2. **Language Switching**: Users can switch languages using the dropdown in the navbar
   - Desktop: Language dropdown in the header
   - Mobile: Language dropdown in the mobile drawer
   - Selected language is stored in session

3. **Using Translations in Blade Templates**:
   ```blade
   {{ __('nav.home') }}
   {{ __('contact.title') }}
   {{ __('product.buy_now') }}
   ```

4. **RTL Support**: Arabic language automatically applies RTL (right-to-left) direction to the layout

## Available Translation Keys

### Navigation
- `nav.home`, `nav.about`, `nav.contact`, `nav.cart`, `nav.my_orders`, `nav.search_placeholder`, `nav.menu`

### Currency & Language
- `currency.title`, `currency.usd`, `currency.dzd`
- `language.title`, `language.en`, `language.ar`, `language.fr`

### Profile Menu
- `profile.account`, `profile.my_account`, `profile.my_orders`, `profile.notifications`, `profile.logout`, `profile.login`, `profile.signup`

### Products & Checkout
- `product.buy_now`, `product.add_to_cart`, `product.diamonds`, etc.
- `checkout.title`, `checkout.user_info`, `checkout.order_summary`, etc.

### Contact Page
- `contact.title`, `contact.name`, `contact.email`, `contact.subject`, `contact.message`, `contact.send`

### Common Elements
- `common.loading`, `common.success`, `common.error`, `common.yes`, `common.no`, etc.

## Adding New Translations

1. Open all three language files in `resources/lang/`
2. Add the same key to all files with appropriate translations
3. Use the key in your Blade templates with `{{ __('category.key') }}`

Example:
```json
// en.json
{
  "product": {
    "new_feature": "New Feature"
  }
}

// fr.json
{
  "product": {
    "new_feature": "Nouvelle Fonctionnalité"
  }
}

// ar.json
{
  "product": {
    "new_feature": "ميزة جديدة"
  }
}
```

Then in Blade:
```blade
{{ __('product.new_feature') }}
```

## Language Route

The language switching route is: `/language/{locale}` where locale is `en`, `fr`, or `ar`.
