# User UI – API‑Driven CRUD (Laravel)

A small Laravel app that renders Blade pages for **User** CRUD while delegating all data operations to an external REST API via a lightweight `ApiClient`.

---

## Features

* Create, read, update, delete Users via external API
* Server‑rendered Blade views (form + list + detail)
* Flash messages for success/error
* Validation error forwarding from API (HTTP 422)
* Conflict handling (HTTP 409)
* Not‑found handling (HTTP 404)
* Feature tests for controller/UI flows

---

## Tech Stack

* PHP 8.2+
* Laravel 10/11
* Blade Templates
* PHPUnit / `php artisan test`

---

## Requirements

* PHP 8.2+ with extensions required by Laravel
* Composer
* Node.js (only if you plan to compile assets; not required for basic Blade)

---

## Quick Start

```bash
# 1) Install dependencies
composer install

# 2) Env & app key
cp .env.example .env
php artisan key:generate

# 3) Configure API endpoint (see **Environment** below)

# 4) Run the app
php artisan serve
# App runs at http://127.0.0.1:8000

# 5) Run tests
php artisan test
```

---

## Environment

Configure these variables in `.env`:

```env
APP_NAME="User UI"
APP_ENV=local
APP_KEY=base64:GENERATED_BY_ARTISAN
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# External API (User Service)
API_BASE_URL=http://127.0.0.1:9999/api
API_TIMEOUT=10           # seconds
API_BEARER_TOKEN=        # optional; set if your API needs Authorization: Bearer <token>

# Session/cache (use defaults for local)
SESSION_DRIVER=file
CACHE_DRIVER=file
LOG_CHANNEL=stack
```

> **Note**: No local DB is required for the UI itself unless you add features that persist data in Laravel. Sessions use the file driver by default.

---

## Routing (typical)

* `GET /users/create` – Show create form and list of users
* `POST /users` – Create user via API
* `GET /users/{id}` – Show a single user
* `PATCH /users/{id}` – Update user via API
* `DELETE /users/{id}` – Delete user via API

Your `UserUiController` calls `ApiClient` which wraps Guzzle/HTTP to forward requests and return JSON to the UI layer.

---

## Validation & Error Handling

* **422 Unprocessable Entity**: API field errors are forwarded and shown under inputs.
* **409 Conflict**: (e.g., duplicate email) shows a flash error on the create/update page.
* **404 Not Found**: Renders a 404 page if the API cannot find the user.
* **5xx**: Shows a generic error flash; check logs.

---

## Testing

Run all feature tests:

```bash
php artisan test --testsuite=Feature
# or run a single test class
php artisan test --filter=UserUiControllerTest
```

Make sure `.env.testing` has valid API settings (pointing to a test/stub server) if tests hit the real API.

---

## Project Structure (relevant parts)

```
app/Http/Controllers/UserUiController.php
app/Services/ApiClient.php
resources/views/users/create.blade.php
routes/web.php
tests/Feature/UserUiControllerTest.php
```

---

## Conventions

* Keep Blade templates dumb: they bind to arrays returned by `ApiClient`.
* Centralize all HTTP calls in `ApiClient` for easier testing.
* Use named routes for redirects and flash messages.

---

## Troubleshooting

* **500 on form page**: clear compiled views and caches

  ```bash
  php artisan view:clear && php artisan cache:clear && php artisan config:clear
  ```
* **Validation messages not showing**: ensure the controller redirects back with `withErrors($validator)` or forwards API 422 error payload to the view.
* **Cannot reach API**: verify `API_BASE_URL`, network access, and any `API_BEARER_TOKEN`.

---

## License

MIT (or align with your organization’s standard).
