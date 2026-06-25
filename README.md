# Ticket — REST API

A **Laravel 10** REST API for managing support tickets with role-based access control, token authentication, and JSON:API-compliant responses.

---

## What This Project Demonstrates

This project was built to practice and demonstrate the following backend engineering skills:

- Designing and building a versioned RESTful API (`/api/v1/...`)
- Implementing **token-based authentication** with Laravel Sanctum
- Enforcing **role-based authorization** using Laravel Policies and fine-grained token abilities
- Structuring API responses in **JSON:API format** using API Resources
- Applying **Form Request** classes for validation and input mapping
- Writing **dynamic query filters** for flexible data retrieval
- Setting up **database relationships**, factories, and seeders
- Generating **API documentation** with the Scribe package
- Applying **error handling** with consistent response helpers

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 10 |
| PHP Version | 8.1+ |
| Authentication | Laravel Sanctum (API tokens) |
| Database | MySQL |
| API Documentation | Scribe |
| Testing | PHPUnit |

---

## Core Features

### Authentication
- `POST /api/login` — Authenticate and receive a time-bound API token (30 days)
- `POST /api/register` — Register a new user account
- `POST /api/logout` — Revoke the current token

### Ticket Management (`/api/v1/tickets`)
Full CRUD for support tickets:
- **List** tickets with filtering by title, status, and date ranges
- **Create** a ticket (users create their own; managers can create on behalf of others)
- **Show** a single ticket, optionally loading the author via `?include=author`
- **Update** (`PATCH`) a ticket — partial field updates
- **Replace** (`PUT`) a ticket — full field replacement
- **Delete** a ticket

### User Management (`/api/v1/users`)
Manager-only access:
- **List**, **create**, **show**, **update**, **replace**, and **delete** users
- Show a user with their tickets via `?include=tickets`

### Author Routes (`/api/v1/authors`)
- List only users who have created at least one ticket (uses a `DISTINCT JOIN`)
- Manage tickets scoped to a specific author via `/api/v1/authors/{author}/tickets`

---

## Authorization Design

Two roles are supported: **Manager** and **Regular User**. Permissions are granted at token creation time via Sanctum token abilities.

| Ability | Manager | Regular User |
|---|---|---|
| `ticket:create` (any user) | Yes | No |
| `ticket:own:create` | Yes | Yes |
| `ticket:update` (any ticket) | Yes | No |
| `ticket:own:update` | Yes | Yes |
| `ticket:replace` (any ticket) | Yes | No |
| `ticket:delete` (any ticket) | Yes | No |
| `ticket:own:delete` | Yes | Yes |
| `user:create / update / replace / delete` | Yes | No |

Authorization is enforced at the **policy layer** (`TicketPolicy`, `UserPolicy`) — not just in routes — so every controller action is independently protected.

---

## API Response Format

All responses follow the **JSON:API specification**:

```json
{
    "data": {
        "type": "ticket",
        "id": 1,
        "attributes": {
            "title": "Cannot log in",
            "status": "A",
            "description": "Login page returns 500 error.",
            "createdAt": "2024-06-04T08:14:09.000000Z"
        },
        "relationships": {
            "author": {
                "data": { "type": "user", "id": 3 }
            }
        }
    }
}
```

Ticket status codes: `A` (Active) · `C` (Closed) · `H` (On Hold) · `X` (Cancelled)

---

## Filtering & Sorting

Tickets and users support query parameter filtering:

```
GET /api/v1/tickets?filter[status]=A,C&filter[title]=login&sort=-createdAt
GET /api/v1/users?filter[email]=@example.com&sort=name
```

Filtering is implemented via reusable `QueryFilter` classes (`TicketFilter`, `AuthorFilter`) that dynamically map query parameters to Eloquent scopes.

---

## Project Structure Highlights

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php          # Login / Logout
│   │   └── V1/
│   │       ├── ApiController.php       # Base: include() + isAble() helpers
│   │       ├── TicketController.php
│   │       ├── UserController.php
│   │       ├── AuthorsController.php
│   │       └── AuthorTicketsController.php
│   ├── Requests/Api/V1/               # Form Requests (validate + map input)
│   └── Resources/V1/                  # API Resources (JSON:API format)
├── Models/
│   ├── User.php                        # HasMany tickets, HasApiTokens
│   └── Ticket.php                      # BelongsTo user (author)
├── Policies/V1/
│   ├── TicketPolicy.php
│   └── UserPolicy.php
├── Permissions/V1/
│   └── Abilities.php                   # Token ability constants
└── Filters/V1/
    ├── QueryFilter.php                 # Abstract base filter
    ├── TicketFilter.php
    └── AuthorFilter.php

routes/
├── api.php                             # Auth routes
└── api_v1.php                          # Versioned resource routes

database/
├── migrations/                         # users, tickets, personal_access_tokens
├── factories/                          # UserFactory, TicketFactory
└── seeders/                            # 10 users, 100 tickets, 1 manager
```

---

## Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- MySQL

### Installation

```bash
git clone <repo-url>
cd ticket-please
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then:

```bash
php artisan migrate --seed
php artisan serve
```

A manager account is seeded automatically:
- **Email:** `manager@manager.com`
- **Password:** `password`

### API Documentation

```bash
php artisan scribe:generate
```

Documentation will be available at `http://localhost:8000/docs`.

---

## Key Concepts Practiced

- **API Versioning** — Routes are namespaced under `/api/v1/` for forward-compatible design
- **Sanctum Token Abilities** — Fine-grained permissions granted per token, not just per role
- **Policy-Based Authorization** — Business rules are isolated from controllers
- **JSON:API Format** — Consistent, spec-compliant response structure
- **Form Requests** — Input validation and mapping decoupled from controllers
- **QueryFilter Pattern** — Extensible dynamic filtering via query parameters
- **Database Seeders + Factories** — Realistic test data for development
- **Scribe Documentation** — Auto-generated, browsable API docs
