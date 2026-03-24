# Changelog

All notable changes to `rapyd` will be documented in this file.

## 1.1.0 - 2026-03-24

- Add Laravel 13 support
- Add Pest 4 and Orchestra Testbench 11 support
- Widen dependency constraints for pestphp/pest-plugin-arch and pestphp/pest-plugin-laravel

## 1.0.0 - 2026-03-24

- Initial release
- Full Rapyd API coverage across 6 domains (Collect, Disburse, Wallet, Issuing, Verify, Protect)
- HMAC-SHA256 request signing
- Typed DTOs with PHP 8.2+ enums (24 enums, 24 DTOs)
- Webhook signature verification and event dispatch (65 event types)
- Auto-pagination with LazyCollection
- Artisan commands: rapyd:test-connection, rapyd:list-payment-methods, rapyd:webhook-info
