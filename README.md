<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Digiflazz configuration

If you wish to use Digiflazz as the top-up provider, set the following environment variables in your `.env` file:

- `DIGIFLAZZ_USERNAME` - your Digiflazz username
- `DIGIFLAZZ_SIGN` - your Digiflazz API sign/key
- `DIGIFLAZZ_BASE_URL` - optional, defaults to `https://api.digiflazz.com/v1`

When `DIGIFLAZZ_USERNAME` and `DIGIFLAZZ_SIGN` are present, the app will route top-up requests to Digiflazz automatically.

### Webhook configuration

To receive Digiflazz status updates, configure a webhook URL in your Digiflazz dashboard to point to:

	POST https://your-domain.example/webhook/digiflazz

Set a webhook secret in your `.env` file to validate payloads:

- `DIGIFLAZZ_WEBHOOK_SECRET` - secret used to validate `X-Hub-Signature` header (HMAC-SHA1). This application enforces the presence of this secret in non-local environments and will reject webhook requests without a valid signature.

The webhook will contain a JSON payload with a top-level `data` object (see Digiflazz docs). The application will save events to a new `digiflazz_statuses` table and try to associate them with the corresponding `orders` record automatically.

Database migration:

Run the migration to create the `digiflazz_statuses` table:

```bash
php artisan migrate
```

Status updates — webhook-only (no WebSocket or client polling)
----------------------------------------------------------------

Per project policy, order status changes are driven exclusively by Digiflazz webhook callbacks. The server persists Digiflazz events to `digiflazz_statuses` and updates the corresponding `orders` record; clients should read the order status from the server when appropriate (e.g., page load or manual refresh).

Notes and operational guidance:

- Do NOT rely on WebSocket broadcasting or client-side polling for status updates — those mechanisms have been removed from the codebase by design.
- The application will not trigger Digiflazz requests from client-side status checks. All server-initiated Digiflazz calls are made only during the initial top-up submission and are protected by server-side duplicate checks.
- To test end-to-end, configure `DIGIFLAZZ_WEBHOOK_SECRET` and point Digiflazz webhook to `/webhook/digiflazz`; then simulate payloads to verify `digiflazz_statuses` creation and `orders` status transitions.

If you need real-time push notifications in the future, consider re-introducing a broadcasting driver and client Echo wiring as a separate opt-in feature, but note this repository currently enforces webhook-only status flow.

