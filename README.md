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
Update .env with the application's local settings and MySQL connection values.

Set the MySQL connection values in `.env`.

The `.env` file contains environment-specific configuration and must not be committed to Git.

### 2. Install Dependencies
Install PHP dependencies:
```bash
composer install
```

If the application uses the included frontend dependencies, install them with:
```bash
npm install
```

### 3. Create the MySQL Database and User 
Log into MySQL as an administrative user:
```bash
mysql -u root -p
```

Create the application database:

```sql
CREATE DATABASE IF NOT EXISTS my_database;
```

Create the application-specific MySQL user:

```sql
CREATE USER IF NOT EXISTS 'my_user'@'localhost'
IDENTIFIED BY 'YOUR_PASSWORD';
```

Grant the user access to the application database:

```sql
GRANT ALL PRIVILEGES ON my_database.* TO 'my_user'@'localhost';
```

Apply the privileges:

```sql
FLUSH PRIVILEGES;
```

Verify the database exists:

```sql
SHOW DATABASES;
```

Verify the user exists:

```sql
SELECT User, Host
FROM mysql.user
WHERE User = 'my_user';
```

Exit MySQL:

```sql
exit
```

The database name, username, and password created here must match the corresponding DB_* values in .env.

4. Verify the Database Login

Before running the application migrations, verify that the new MySQL user can connect to the new database:

```bash
mysql -u my_user -p my_database
```

Enter the password configured in .env.

After connecting successfully, verify the selected database:

```sql
SELECT DATABASE();
```

It should return:

my_database

Exit MySQL:

```sql
exit
```

5. Run Database Migrations

From the application's root directory, run:

```bash
php migrate.php
```

The migration process creates the required foundation tables and applies the SQL migration files in:

database/migrations/
6. Start the Local PHP Server

Start the development server from the application root:

```bash
php -S localhost:8000 -t public
```

The application will then be available at:

http://localhost:8000

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
    ```bash
    git init
    ```
4. Create the new application's `.env` file.
    ```bash
    cp .env.example .env
    ```
5. Create a separate database for the new application.
    Navigate to your app's root directory in terminal.
    mysql -u root -p -e "CREATE DATABASE your_database_name;"
    USE your_database_name;
6. Set the new application name and database configuration.
7. Install PHP dependencies.
    ```bash
    composer install
    ```
    
   Install frontend dependencies if required:
    ```bash
   npm install
    ```
8. Verify the new MySQL user's ability to connect to the new database:
    ```bash
    mysql -u your_user -p your_database
    ```
9. Run the database migrations.
    ```bash
    php migrate.php
    ```
10. Run the complete test suite.
    ```bash
    php tests/run.php
    ```
11. Start the local development server.
    ```bash
    php -S localhost:8000 -t public
    ```
12. Verify that the application loads successfully.
13. Commit the clean starting point.
    ```bash
    git add .
    git commit -m "Initialize application from multi-tenant foundation"
    ```
14. Create and connect the new GitHub repository.
15. Begin building the application-specific functionality.

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
