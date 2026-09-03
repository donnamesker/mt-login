# Application Foundation

This project is a reusable multi-tenant PHP application foundation. It is intentionally small and framework-free so it can serve as the starting point for different applications.

## Core hierarchy

```text
User
  |
  +-- Tenant membership + role
  |
  +-- Tenant
        |
        +-- Business
              |
              +-- Business membership + role
```

A tenant represents the customer/account boundary. A business represents an operational unit inside that tenant.

## Security rules

### Tenant access

A user must have a row in `tenant_users` to access a tenant.

Tenant roles:

- `owner`
- `admin`
- `member`

Owners and administrators can manage the tenant. Members have access but do not have tenant-management authority.

### Business access

A business always belongs to exactly one tenant.

Tenant owners and administrators can access and manage every business in their tenant.

Tenant members must have explicit membership in `business_users` to access a business.

Business roles:

- `owner`
- `admin`
- `member`

Business owners and administrators can manage the business. Business members are view-only unless a future application grants additional permissions.

Business administrators cannot promote members to owner or administrator and cannot modify or remove existing owners or administrators.

The last owner of a tenant or business cannot be removed or demoted.

## Authorization boundary

`AuthorizationService` is the central permission layer. Application services should use it before performing operations involving tenant or business data.

Business membership is never treated as sufficient by itself. The user must also remain a member of the parent tenant. This protects against stale membership records and makes tenant isolation explicit.

## Session context

The session stores:

- authenticated `user_id`
- selected `tenant_id`
- selected `business_id`

A login regenerates the session ID and clears any previous tenant/business context. This prevents context from one authenticated identity being reused after another login.

`ContextService` validates the selected tenant and business against the authenticated user before returning application context or allowing a context switch.

## Controllers and services

Authenticated application controllers extend `AuthenticatedController` and obtain a validated context through `requireContext()`.

The following controllers are intentionally outside that base because they establish or change authentication/context:

- login/register
- onboarding
- context switching

Business membership operations are separated from general business operations in `BusinessUserService` and `BusinessUserController`.

## Data isolation rule

Never trust tenant, business, or user IDs supplied by the browser.

IDs may be supplied as request parameters for navigation or selection, but the service layer must validate that the authenticated user is authorized for the requested tenant/business before reading or changing data.

When adding a new application module, follow this pattern:

```text
Request
  -> Controller
  -> Authenticated context
  -> Service
  -> Authorization check
  -> Model/data operation
```

Do not bypass the service/authorization layer for user-facing operations.

## Testing

Run the complete test suite from the project root with:

```bash
php tests/run.php
```

The suite includes authentication, authorization, tenant/business isolation, context switching, CSRF, onboarding, session context, sessions, and user-model coverage.

The authorization tests intentionally verify that losing tenant membership invalidates business access even if a business membership record remains.

## Development principle

Keep the foundation generic. Domain-specific features should be added on top of this layer rather than changing the tenant, authentication, session, or authorization architecture for each new application.
