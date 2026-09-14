# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

This is a **Laravel application** that self-hosts [Mailcoach](https://mailcoach.app) (`spatie/laravel-mailcoach` v10, installed in `vendor/`) for a tenant/property business ("Cove") — used to email tenants, tagging people `ex-tenant` when they move out based on a `move_out_date` subscriber extra attribute.

This repo is thin by design: almost all domain logic (campaigns, automations, subscribers, templates, sending) lives in the `spatie/laravel-mailcoach` package under `vendor/spatie/laravel-mailcoach`. **That package has its own `CLAUDE.md`** (`vendor/spatie/laravel-mailcoach/CLAUDE.md`) — read it when working with anything Mailcoach-domain (Audience, Campaign, Automation, TransactionalMail, etc.). This repo's own `app/` only contains:

- Auth screens (login/forgot-password/reset/welcome) wrapping Mailcoach's auth
- User management (`Livewire/UsersComponent`, `EditUserComponent`, `CreateUserComponent`, `AccountComponent`)
- A handful of scheduled console commands for tenant tagging (see below)
- Service providers wiring Mailcoach's menu (`app/Listeners/SetupMailcoach.php`) and Horizon

## Commands

```bash
# Install
composer install
npm install

# Dev servers
php artisan serve
npm run dev             # Vite (Tailwind v4 + JS)
php artisan horizon     # queue worker (required for Mailcoach sending/automations)

# Build frontend
npm run build

# Tests (plain PHPUnit, not Pest — this app doesn't use Pest, the vendor package does)
vendor/bin/phpunit
vendor/bin/phpunit --filter test_name
vendor/bin/phpunit tests/Feature/ExampleTest.php

# Code style
vendor/bin/pint
```

Local dev uses Laravel Sail (`docker-compose.yml`: app + MySQL + Redis).

## Architecture

### App-vs-package split

Mailcoach's own tables, models, routes (`routes/mailcoach-*.php`), and UI are registered by the package's service provider — they are **not** published into this repo. `database/migrations/` here only has the base Laravel tables (`users`, `jobs`, `cache`, `personal_access_tokens`); Mailcoach's schema migrates straight from `vendor/spatie/laravel-mailcoach/database/migrations` via `hasMigrations()` (spatie/laravel-package-tools), so `php artisan migrate` picks both up automatically.

`config/mailcoach.php` in this repo only overrides pruning intervals (`MAILCOACH_PRUNE_*_AFTER_DAYS` env vars) — everything else uses the package's defaults. Check the package's own `config/mailcoach.php` (vendor) for the full set of overridable options (models, actions, editors, middleware) before assuming something needs a new override here.

`AppServiceProvider::bootRoute()` mounts Mailcoach's routes with `Route::mailcoach('/')` and defines the `api` rate limiter. Custom routes for account/users live in `routes/web.php` and reuse `config('mailcoach.middleware.web')` plus `BootstrapSettingsNavigation` so they render inside Mailcoach's own settings nav (wired up via `SetupMailcoach` listening on `ServingMailcoach`).

### Tenant-tagging commands (`app/Console/Commands/`)

Business-specific, not part of Mailcoach: `TagMovedOutTenants` and `RemoveExTenantTag` read/write a subscriber extra attribute (`move_out_date`) and a `ex-tenant` tag, scheduled daily in `routes/console.php`. When touching subscriber-tag bulk operations, prefer `eachById()` over `chunkById()`/manual chunking — a past incident (`f5629d3`) replaced `chunkById` with `eachById` because it's safer under concurrent tag mutation.

### Scheduled jobs

All Mailcoach send/automation/statistics cron jobs plus the two tenant-tagging commands are registered in `routes/console.php`. Horizon (`horizon:snapshot` every 5 min) runs the actual queue workers — nothing sends without Horizon running.

## Working notes

- Local DB is empty — errors reported by the user are from **production**, not something to reproduce against localhost.
- When bumping `spatie/laravel-mailcoach`, check git history first: this repo has pinned/reverted that dependency more than once (see commits around `2353d15`/`c8a3839`) due to issues found after upgrading — verify a version change against the package's CHANGELOG before bumping again.
