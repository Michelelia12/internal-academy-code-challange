# Internal Academy

> A platform for managing company workshops and employee registrations — built with Laravel, Vue.js, and Inertia.js.

---

## Table of Contents

- [Internal Academy](#internal-academy)
  - [Table of Contents](#table-of-contents)
  - [Overview](#overview)
  - [Tech Stack](#tech-stack)
  - [Features](#features)
    - [Must Have](#must-have)
    - [Nice to have Features](#nice-to-have-features)
    - [Top Player Features](#top-player-features)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [Database Setup \& Seeders](#database-setup--seeders)
  - [Running the Application](#running-the-application)
  - [Running Tests](#running-tests)
  - [Manual Testing Guide](#manual-testing-guide)
  - [Artisan Commands](#artisan-commands)
    - [`php artisan academy:remind`](#php-artisan-academyremind)
  - [Real-Time Features](#real-time-features)
  - [Architectural Decisions](#architectural-decisions)
    - [Why Inertia.js?](#why-inertiajs)
    - [Role System — Gates + Middleware, not Spatie](#role-system--gates--middleware-not-spatie)
    - [Waiting List — Database Queue, not Events](#waiting-list--database-queue-not-events)
    - [Overlap Detection — SQL-Level Check](#overlap-detection--sql-level-check)
    - [Testing — PHPUnit with strict coverage](#testing--phpunit-with-strict-coverage)
  - [Git Conventions](#git-conventions)
  - [API Reference](#api-reference)
  - [License](#license)

---

## Overview

Internal Academy is an internal company platform that allows employees to discover, register for, and attend technical (and non-technical) workshops. Admins (HR/Managers) can manage the full workshop lifecycle, while employees can browse, register, and join waiting lists.

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | Laravel 13.x (PHP 8.3)            |
| Frontend   | Vue.js 3.x (Composition API)      |
| Bridge     | Inertia.js                        |
| Database   | SQLite (default) / MySQL          |
| Real-Time  | Laravel Reverb (WebSockets)       |
| Testing    | PHPUnit                           |
| Queue      | Laravel Queue (database driver)   |
| Mail       | SMTP / Mailtrap (dev)             |

---

## Features

- **Roles and Authentication**
Implement two distinct roles: Admin (HR/Manager) and Employee (Developer/Generic User).
Users must be able to log in and see different interfaces based on their role.
- **Workshop Management (Admin)**
The Admin can create, modify, and delete Workshops.
- **Each Workshop**
must have:
  - Title
  - Description
  - Date and Time
  - Maximum Number of Seats (Capacity)
- **Registration and Participation (Employee)**
  - All employees can view the list of future workshops on their dashboard.
  - An employee can sign up for a workshop with one click, but only if there are still available seats.
  - An employee can cancel their registration if they change their mind, immediately freeing up the spot for someone else.
- **The Waiting List**: If a workshop is full, the user is not rejected but can sign up for the "Waiting List." If a confirmed participant cancels their registration, the first user on the waiting list is automatically promoted to participant (manage FIFO logic).
- **No Ubiquity**: Prevent a user from signing up for two workshops that overlap in time. No one can be in two places at once!
- **Command Line Reminder**: Create a custom artisan command (e.g., php artisan academy:remind) that, when launched, sends a reminder email to all participants of workshops scheduled for the following day.
- **Admin Statistics Dashboard** — Shows the most popular workshop and total registration count
- **Real-Time Registration Counter** — Uses Laravel Reverb (WebSockets) to push live updates to the admin dashboard without page refresh
- **Full Test Suite** — Unit tests and feature tests written with PHPUnit, 100% coverage enforced

---

## Requirements
**Without Docker:**
- PHP >= 8.3 with extensions: `curl`, `mbstring`, `sqlite3`/`mysql`, `xml`, `zip`, `pcov`
- Composer
- Node.js 22 & npm
- SQLite (included in PHP) or a MySQL 8+ server
- (Optional) A mail provider or [Mailtrap](https://mailtrap.io) for email testing

**With Docker / DevContainer:** just Docker Desktop — everything else is baked into the image.

---

## Docker & DevContainer

The repository ships with a ready-to-use Docker setup that works both as a **VS Code DevContainer** and as a plain **Docker Compose** environment. No local PHP, Composer, or Node installation required.

### What's inside the image

Built on `ubuntu:jammy`, the `Dockerfile.devcontainer` installs in layers:

| Layer | Contents |
|---|---|
| `devcontainer-base` | curl, git, git-lfs, ssh, unzip, git-aware prompt |
| `devcontainer-php` | PHP 8.3 + extensions (curl, mbstring, intl, SQLite3, MySQL, XML, ZIP, GD, Xdebug, pcov, uuid, imagick), Composer, `php artisan` autocomplete |
| `devcontainer-php-node` | Node.js 22 (via NodeSource) |

The container runs as the non-root `ubuntu` user (UID/GID 1000).

### Exposed ports

| Port | Service |
|---|---|
| `80` (or `$APP_PORT`) | Laravel (`php artisan serve --port=80`) |
| `5173` | Vite dev server |


### Option A — VS Code DevContainer (recommended)

Prerequisites: [Docker Desktop](https://www.docker.com/products/docker-desktop/) and the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).

```bash
# 1. Clone the repo
git clone https://github.com/your-username/internal-academy.git
cd internal-academy

# 2. Open in VS Code
code .
```

VS Code will detect `.devcontainer/devcontainer.json` and prompt **"Reopen in Container"** — click it. The image builds once and you land inside a fully configured shell with PHP, Composer, Node, and `php artisan` autocomplete ready to go.

Your local `~/.ssh` keys are mounted read-only into the container so git operations over SSH work out of the box.

Then continue from [Installation](#installation) — run all commands inside the container terminal.

### Option B — Plain Docker Compose

No VS Code required.

```bash
# 1. Clone the repo
git clone https://github.com/your-username/internal-academy.git
cd internal-academy

# 2. Build and start the container (detached)
docker compose -f docker/docker-compose-dev.yml up -d --build

# 3. Open a shell inside the container
docker exec -it internal-academy-code-challange-devcontainer bash

# 4. All subsequent commands run inside this shell
```

To stop the container:

```bash
docker compose -f docker/docker-compose-dev.yml down
```

### Notes

- The workspace folder on your host is bind-mounted to `/home/ubuntu/workspace` inside the container, so edits in your editor and inside the container are always in sync.
- SQLite is the default database and requires no extra service — the `.sqlite` file lives inside the workspace mount and persists across container restarts.
- If you prefer MySQL, spin up an additional service in `docker-compose-dev.yml` and update `DB_*` variables in `.env` accordingly.

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/your-username/internal-academy.git
cd internal-academy

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy the environment file
cp .env.example .env

# 5. Generate the application key
php artisan key:generate
```

---

## Configuration

Open `.env` and adjust the following values:

```dotenv
# Application
APP_NAME="Internal Academy"
APP_URL=http://localhost:8000

# Database (SQLite — default, no setup needed)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite   ← created automatically

# --- OR use MySQL ---
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=internal_academy
# DB_USERNAME=root
# DB_PASSWORD=secret

# Mail (use Mailtrap for local development)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=academy@yourcompany.com
MAIL_FROM_NAME="Internal Academy"

# Laravel Reverb (WebSockets — for real-time dashboard)
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

## Database Setup & Seeders

```bash
# Run migrations
php artisan migrate

# Seed the database with demo data
php artisan db:seed
```

The seeder will create:

| Role     | Email                        | Password   |
|----------|------------------------------|------------|
| Admin    | admin@academy.test           | password   |
| Employee | alice@academy.test           | password   |
| Employee | bob@academy.test             | password   |
| Employee | charlie@academy.test         | password   |

It will also generate **10 upcoming workshops** with randomized capacity and registration data, including some full workshops with waiting list entries.

---

## Running the Application

Open **four terminal tabs** (or use a process manager like [Laravel Herd](https://herd.laravel.com)):

```bash
# Tab 1 — Laravel development server
php artisan serve

# Tab 2 — Vite (Vue/JS assets)
npm run dev

# Tab 3 — Queue worker (handles email notifications & waiting list promotion)
php artisan queue:work

# Tab 4 — Laravel Reverb WebSocket server (for real-time dashboard)
php artisan reverb:start
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

---

## Running Tests

```bash
# Run the full PHP test suite
php artisan test

# Run with coverage report
php artisan test --coverage

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run the frontend test suite (Vitest, with coverage)
npm test
```

The PHP test suite covers:

- Workshop CRUD (admin only)
- Registration and cancellation flows
- Seat capacity enforcement
- Waiting list promotion (FIFO)
- Overlap/ubiquity validation
- Role-based access control
- Artisan reminder command output
- Real-time event broadcasting

> Tests use an in-memory SQLite database (`:memory:`) and do not affect your local data.

---

## Manual Testing Guide

After running `php artisan db:seed`, you can manually verify every feature from the spec using the accounts below (password: `password`):

| Role     | Email                  |
|----------|------------------------|
| Admin    | admin@academy.test     |
| Employee | alice@academy.test     |
| Employee | bob@academy.test       |
| Employee | charlie@academy.test   |

### 1. Authentication & Roles
1. Log in as `admin@academy.test` → you land on the **Admin dashboard** (workshop management + statistics).
2. Log out, log in as `alice@academy.test` → you land on the **Employee dashboard** (upcoming workshops list).

### 2. Workshop Management (Admin)
1. Log in as `admin@academy.test`.
2. Create a new workshop — fill in title, description, date/time, and capacity.
3. Edit the workshop and change the title.
4. Delete the workshop — it disappears from both the admin and employee views.

### 3. Registration & Cancellation (Employee)
1. Log in as `alice@academy.test`.
2. Register for any workshop that still has available seats — the button changes to **Cancel**.
3. Cancel the registration — the seat is freed and the button reverts to **Register**.

### 4. Waiting List
The seeder creates **Charlie's Workshop** (capacity 1, Charlie is the only confirmed participant).

1. Log in as `alice@academy.test` (or `admin@academy.test`) and register for **Charlie's Workshop** → status shows **Waiting** (workshop is full).
2. Log out and log in as `charlie@academy.test`.
3. Cancel Charlie's registration.
4. Log back in as `alice@academy.test` → status is now **Confirmed** (automatic FIFO promotion).

### 5. Overlap Detection
The seeder pre-registers **charlie@academy.test** for **[Overlap QA] Session A** (09:00–11:00).

1. Log in as `charlie@academy.test`.
2. Try to register for **[Overlap QA] Session B** (10:00–12:00, overlaps by 1 hour).
3. A toast notification appears: *"You are already registered for an overlapping workshop."*

### 6. Statistics Dashboard & Real-Time Counter
Requires Reverb running (see [Real-Time Features](#real-time-features)).

1. Open the admin dashboard (`admin@academy.test`) in one browser tab.
2. Open the employee dashboard (`alice@academy.test`) in a second tab.
3. Register for a workshop as Alice → the registration counter on the admin dashboard increments **without a page refresh**.

### 7. Command Line Reminder (`academy:remind`)
The seeder creates **[Reminder QA] Tomorrow's Workshop** scheduled for tomorrow with `alice@academy.test` confirmed. No extra setup needed.

**Run the command:**
```bash
php artisan academy:remind
```

**Verify the email:**

With the default `MAIL_MAILER=log`, the email is appended to `storage/logs/laravel.log`:
```bash
grep -A 20 "WorkshopReminder" application/storage/logs/laravel.log | tail -20
```
You should see an entry addressed to `alice@academy.test` for **[Reminder QA] Tomorrow's Workshop**.

> To inspect the email in a real inbox, point `.env` at [Mailpit](https://mailpit.axllent.org): set `MAIL_MAILER=smtp`, `MAIL_HOST=localhost`, `MAIL_PORT=1025`, and open `http://localhost:8025`.

---

## Artisan Commands

### `php artisan academy:remind`

Sends a reminder email to all confirmed participants of workshops scheduled for **tomorrow**.

```bash
php artisan academy:remind
```

You can also schedule this command to run automatically by adding it to `routes/console.php`:

```php
Schedule::command('academy:remind')->dailyAt('08:00');
```

Then start the scheduler:

```bash
php artisan schedule:work
```

---

## Real-Time Features

The admin Statistics Dashboard uses **Laravel Reverb** (WebSockets) to broadcast a `RegistrationUpdated` event whenever a participant registers or cancels. Vue 3 listens on the `academy` channel via Echo and updates the registration counter live — no page refresh needed.

If you prefer not to run Reverb, the dashboard degrades gracefully: it falls back to showing the last-known count from the server.

---

## Architectural Decisions

### Why Inertia.js?

Inertia lets us use Vue 3 for rich, reactive interfaces without building a separate REST API. Controllers return Inertia responses (which are just Vue page components + props), keeping the backend and frontend in the same repo with shared auth and routing logic. This fits the 2-day scope well.

### Role System — Gates + Middleware, not Spatie

For two roles (Admin / Employee), a dedicated package like `spatie/laravel-permission` would be overkill. Instead, roles are stored as an `is_admin` boolean on the `users` table. A `EnsureAdmin` middleware guards all admin routes. Gates handle policy checks where needed. This is easy to reason about and easy to extend later.

### Waiting List — Database Queue, not Events

The promotion of waiting-list users is handled by a `PromoteFromWaitingList` job dispatched to the **database queue** inside the same DB transaction as the cancellation. If the job dispatch fails, the transaction rolls back and the cancellation is aborted, keeping the data consistent. The queue worker then processes the job asynchronously — sending the promotion notification without blocking the HTTP response. A pure event-driven approach would be cleaner architecturally but harder to keep consistent within a 2-day window.

### Overlap Detection — SQL-Level Check

The "no ubiquity" constraint is enforced with a database query that checks for time overlaps before inserting a registration. The logic correctly handles the edge case where a new workshop starts exactly when another ends (those are allowed).

### Testing — PHPUnit with strict coverage

PHPUnit is used directly (no Pest wrapper). `requireCoverageMetadata` and `beStrictAboutCoverageMetadata` are enabled in `phpunit.xml`, so every test class must declare `#[CoversClass]` or `#[CoversNothing]`. `RefreshDatabase` + factories ensure each test starts from a clean state, and the suite runs in parallel via ParaTest with a 100% coverage gate.

---

docs## Git Conventions

Commits follow the `type(scope): description` format and are kept small (one logical unit per commit).

| Type | When to use |
|------|-------------|
| `feat` | a new feature or behaviour |
| `fix` | a bug fix |
| `test` | adding or correcting tests only |
| `refactor` | code change that is neither a fix nor a feature |
| `docs` | README, comments, or other documentation only |
| `chore` | tooling, config, dependencies, seeders |

Examples:

```
feat(auth): add User model with is_admin cast and unit tests
feat(workshop): add WorkshopController CRUD with form requests and feature tests
feat(registration): add RegistrationController store action and feature tests
feat(waiting-list): add PromoteFromWaitingList job and unit tests
feat(overlap): add OverlapChecker service and feature tests
feat(realtime): add RegistrationUpdated broadcast event and tests
feat(dashboard): add StatisticsController and feature tests
feat(remind): add academy:remind command, mailable, and tests
fix(registration): prevent double-registration race condition
fix(overlap): correct boundary-moment comparison operator
refactor(workshop): extract seat availability check into model method
docs(readme): add Git conventions section
chore(seed): add DatabaseSeeder with users and workshops
chore(deps): update laravel/framework to 13.x
```

A pre-commit hook runs `composer app:quality-checks` (PHPStan + full PHP test suite + strict-types check) followed by `npm test` (Vitest with 100% coverage threshold) before every commit. A commit is rejected if any check fails, so every commit in the history is guaranteed to be green.

---

## API Reference

All routes use Inertia (server-rendered SPA), not a traditional JSON REST API. The meaningful HTTP endpoints are:

| Method | URI                                  | Action                            | Auth       |
|--------|--------------------------------------|-----------------------------------|------------|
| GET    | `/`                                  | Redirect to dashboard             | —          |
| GET    | `/dashboard`                         | Employee workshop list            | Employee   |
| GET    | `/workshops`                         | Admin workshop list               | Admin      |
| POST   | `/workshops`                         | Create workshop                   | Admin      |
| PUT    | `/workshops/{workshop}`              | Update workshop                   | Admin      |
| DELETE | `/workshops/{workshop}`              | Delete workshop                   | Admin      |
| POST   | `/workshops/{workshop}/registrations` | Register (or join waiting list)  | Employee   |
| DELETE | `/workshops/{workshop}/registrations` | Cancel registration              | Employee   |
| GET    | `/admin/stats`                       | Statistics dashboard              | Admin      |

---

## License

MIT — for internal use.



backend/