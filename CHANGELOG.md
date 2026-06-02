# Changelog

All notable changes to `laravel-asana` will be documented in this file.

## v1.0.1 - 2026-06-02

Maintenance release — repository and CI housekeeping. No changes to the package code; functionally identical to v1.0.0 for consumers.

### Changed

- CI: bumped `peter-evans/create-pull-request` to v8 (resolves Node.js 20 runner deprecation)
- CI: bumped `dependabot/fetch-metadata` to v3
- Repository: GitHub Actions can now create PRs and use auto-merge, so automated changelog updates and Dependabot auto-merge work as intended

**Full Changelog**: https://github.com/WMBH/laravel-asana/compare/v1.0.0...v1.0.1

## v1.0.0 - 2026-06-01

First stable release. Security-focused upgrade of the HTTP layer plus Laravel 13 support.

### Security

- Upgraded [Saloon](https://github.com/saloonphp/saloon) to v4, resolving three vulnerabilities in the HTTP layer:
  - CVE-2026-33942 (high) — insecure deserialization in `AccessTokenAuthenticator` (object injection / RCE)
  - CVE-2026-33182 (medium) — SSRF / credential leakage via absolute URL overriding base URL
  - CVE-2026-33183 (medium) — fixture name path traversal
  

### Breaking changes

- `saloonphp/saloon` is now required at `^4.0` (was `^3.0`). If your application also depends on Saloon v3 directly, upgrade it to v4 alongside this package.
- `saloonphp/laravel-plugin` is no longer a dependency of this package (it was unused). If you relied on it transitively (e.g. the `Saloon` facade or artisan generators), require it directly: `composer require saloonphp/laravel-plugin:^4.0`
- `guzzlehttp/guzzle` minimum raised from `^7.0` to `^7.6`

### Added

- Laravel 13 support (`illuminate/contracts: ^13.0`, `orchestra/testbench: ^11.0`)
- CI test matrix now covers PHP 8.3/8.4 × Laravel 11/12/13 × prefer-lowest/prefer-stable on Ubuntu and Windows

The package's own API is unchanged — all `Asana::` resources, DTOs, query builder, and exceptions work exactly as in v0.1.0.

**Full Changelog**: https://github.com/WMBH/laravel-asana/compare/v0.1.0...v1.0.0

## v0.1.0 - 2025-02-25

- Initial release
- Full Asana REST API coverage (Tasks, Projects, Sections, Workspaces, Users, Teams, Tags, Stories, Attachments, Custom Fields, Portfolios, Goals, Webhooks, Batch)
- Fluent task search query builder
- Typed exceptions for API errors
- Personal Access Token authentication
