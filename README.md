# Reusable Multi-Tenant PHP Application Foundation

This project is a reusable foundation for future PHP applications that require authentication, tenant/account separation, business-level separation, roles, session context, and authorization.

It is designed to provide a tested starting point so that future applications can focus on their application-specific functionality rather than rebuilding the underlying authentication, security, session, tenant, and authorization infrastructure.

It is intentionally built with:

* PHP 8.2+
* PDO/MySQL
* Composer
* phpdotenv
* Bootstrap 5
* Minimal custom routing and MVC-style organization
* No full-stack framework

## What This Foundation Provides

The foundation includes the core infrastructure for:

* User authentication
* Session management
* CSRF protection
* Authorization
* Account and tenant context
* Context switching
* User/account relationships
* Business-level separation
* Database connection management
* Database migrations
* MVC-style application organization
* Core automated tests

The goal is to establish a secure and predictable foundation that can be reused across multiple applications.

## Multi-Tenant Architecture

Tenant separation is a fundamental design requirement of this foundation.

Users belong to an account/tenant, and application data belonging to that tenant must remain isolated from data belonging to other tenants.

Future application-specific tables should generally include an account or tenant identifier when the records belong to a particular tenant.

Application queries must enforce the current tenant context. A user should never be able to access another tenant's data simply by changing an ID in a URL, form, request, or API call.

See `docs/FOUNDATION.md` for the detailed tenant hierarchy, authorization rules, session context rules, data-isolation requirements, and conventions for building future application modules.

## Install

### 1. Create the Environment File

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Set the MySQL connection values in `.env`.

The `.env` file contains environment-specific configuration and must not be committed to Git.

### 2. Install Composer Dependencies

```bash
composer install
```

### 3. Run Database Migrations

Create/configure the MySQL database specified in `.env`, then run:

```bash
php migrate.php
```

### 4. Start the Local PHP Server

```bash
php -S localhost:8000 -t public
```

The application will then be available at:

```text
http://localhost:8000
```

## Tests

Run the complete test suite:

```bash
php tests/run.php
```

All tests should pass before using this foundation as the starting point for application-specific development.

The test suite covers the core authentication, session, CSRF, authorization, account/context, onboarding, and user functionality.

## Architecture

The project uses a lightweight MVC-style structure without a full-stack framework.

Key directories include:

```text
app/
├── Controllers/
├── Database/
├── Models/
├── Services/
└── Support/

config/
database/
└── migrations/

public/
resources/
└── views/

routes/
storage/
tests/
docs/
```

The exact contents of these directories may expand as the foundation evolves.

See `docs/FOUNDATION.md` for the detailed architecture and rules that should be followed when extending the application.

## Important Design Rule

This is a **foundation, not a domain-specific application**.

Future domain-specific features should sit on top of the existing authentication, tenant, business, context, and authorization layers rather than modifying those layers for individual features.

For example, a future application might add invoices, projects, clients, appointments, inventory, financial records, or other domain-specific functionality. Those features should use the existing foundation rather than creating their own authentication or tenant-separation logic.

## Using This Foundation for a New Application

When starting a new application from this foundation:

1. Copy the starter application into a new project directory.
2. Remove the copied `.git` directory.
3. Initialize a new Git repository.
4. Create the new application's `.env` file.
5. Create a separate database for the new application.
6. Set the new application name and database configuration.
7. Install dependencies.
8. Run the migrations.
9. Run the complete test suite.
10. Commit the clean starting point.
11. Create and connect the new GitHub repository.
12. Begin building the application-specific functionality.

The new application should have its own Git history, environment configuration, and database.

The starter repository itself should remain a stable, reusable foundation.

## Production

For production deployments:

* Use a production database separate from development databases.
* Configure the production database credentials in `.env`.
* Set `APP_ENV=production`.
* Set `APP_DEBUG=false`.
* Use the production application name.
* Point the web server's document root to the `public` directory.
* Never commit `.env` or other secrets to Git.
* Run the database migrations against the intended production database.
* Verify that the complete test suite passes before deployment.

## Foundation Philosophy

The purpose of this project is to provide a repeatable starting point for future applications.

The foundation should remain intentionally small and focused on infrastructure that is genuinely reusable across applications.

When developing a new application, application-specific functionality should be added to the new application's repository rather than automatically added back into the foundation.

If a feature proves to be broadly reusable across future applications, it can be evaluated for inclusion in a future version of this foundation.
