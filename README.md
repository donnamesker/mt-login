# Reusable Multi-Tenant PHP Application Foundation

This project is the reusable foundation for future PHP applications that require authentication, tenant/account separation, business-level separation, roles, session context, and authorization.

It is intentionally built with:

- PHP 8.2+
- PDO/MySQL
- Composer
- phpdotenv
- Bootstrap 5
- Minimal custom routing and MVC-style organization
- No full-stack framework

## Install

1. Copy `.env.example` to `.env`.
2. Set the MySQL connection values in `.env`.
3. Install Composer dependencies:

```bash
composer install
```

4. Run the database migrations:

```bash
php migrate.php
```

5. Start the local PHP server:

```bash
php -S localhost:8000 -t public
```

## Tests

Run the full test suite:

```bash
php tests/run.php
```

## Architecture

See `docs/FOUNDATION.md` for the tenant hierarchy, authorization rules, session context rules, data-isolation requirements, and conventions for building future application modules.

## Important design rule

This is a foundation, not a bookkeeping application. Future domain-specific features should sit on top of the existing authentication, tenant, business, context, and authorization layers rather than modifying those layers for individual features.
