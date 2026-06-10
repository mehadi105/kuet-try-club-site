# PHP backend setup (TRY KUET)

## Installed on this machine

- **PHP 8.5** via Homebrew (`brew install php`)
- **PostgreSQL 17** via Homebrew (`brew install postgresql@17`)
- PHP extensions used: **PDO**, **pdo_pgsql**, **json**

Verify:

```bash
php -v
php -m | grep pdo_pgsql
brew services list | grep postgresql
```

Add PostgreSQL to your PATH (if `psql` is not found):

```bash
echo 'export PATH="/opt/homebrew/opt/postgresql@17/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

## Project structure

```
api/
  config.php       # App config + .env loader
  database.php     # PostgreSQL connection + table creation
  init-db.php      # One-time database setup script
  submit-join.php  # POST endpoint for join form
  health.php       # GET check that backend works
scripts/
  schema.sql       # Manual SQL schema (optional)
```

## Environment variables

Copy and edit:

```bash
cp .env.example .env
```

| Variable   | Default     | Description        |
|-----------|-------------|--------------------|
| DB_HOST   | 127.0.0.1   | PostgreSQL host    |
| DB_PORT   | 5432        | PostgreSQL port    |
| DB_NAME   | try_kuet    | Database name      |
| DB_USER   | current macOS username (auto-detected) | DB username |
| DB_PASS   | (empty)     | DB password        |

## First-time database setup

1. Start PostgreSQL:

```bash
brew services start postgresql@17
```

2. Initialize database + table:

```bash
cd "/Users/mynulhassanmehadi/Desktop/TRY KUET PROJECT"
php api/init-db.php
```

## Run the site locally

PostgreSQL alone is not enough — you must also start the **PHP web server**.

**Easiest way (recommended):**

```bash
cd "/Users/mynulhassanmehadi/Desktop/TRY KUET PROJECT"
./start.sh
```

**Or manually:**

```bash
brew services start postgresql@17
cd "/Users/mynulhassanmehadi/Desktop/TRY KUET PROJECT"
php -S localhost:8000
```

Keep that terminal window open. Then open in your browser:

- Home: http://localhost:8000/index.html
- Join form: http://localhost:8000/join.html
- Admin: http://localhost:8000/admin/login.php
- API health: http://localhost:8000/api/health.php

If you see “connection refused”, the PHP server is not running — run `./start.sh` again.

**Do not** open `index.html` by double-clicking (file://) — forms and admin need `localhost:8000`.

## Join form API

- **URL:** `POST /api/submit-join.php`
- **Body:** `multipart/form-data` (same fields as the HTML form)
- **Response:** JSON `{ "success": true|false, "message": "..." }`

## Admin panel

Manage posts, spotlight items, volunteer applications, contact messages, subscribers, and site settings.

1. Start PostgreSQL and the PHP server (see above).
2. Open: http://localhost:8000/admin/login.php
3. Default login (change in `.env`):

| Variable | Default |
|----------|---------|
| ADMIN_USERNAME | admin |
| ADMIN_PASSWORD | trykuet123 |

**Admin sections:**
- **Dashboard** — quick stats and recent activity
- **Posts** — homepage story cards
- **Spotlight** — spotlight mini-cards
- **Applications** — join form submissions (status workflow, notes, CSV export)
- **Messages** — contact form inbox
- **Appeal requests** — donation/appeal submissions, review, create post
- **Subscribers** — email subscribe list
- **Site settings** — hero, sections, YouTube playlist, official links

## Public APIs

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/content.php` | GET | Homepage posts, spotlight, settings |
| `/api/submit-join.php` | POST | Volunteer application (multipart; includes profile photo) |
| `/api/check-application-status.php` | POST | Student status lookup (roll + phone) |
| `/api/submit-contact.php` | POST | Contact form |
| `/api/submit-appeal-request.php` | POST | Donation/appeal request (multipart; optional photo) |
| `/api/submit-subscribe.php` | POST | Subscribe email |
| `/api/health.php` | GET | Backend health check |

## View saved applications (PostgreSQL)

```bash
psql -d try_kuet -c "SELECT id, fullname, roll, department, created_at FROM join_applications;"
```

Or use the admin panel: http://localhost:8000/admin/applications.php

### Application workflow

**Statuses:** Pending → Interview scheduled / Waitlisted → Approved or Rejected

- Admin updates status and internal notes at **Applications → Review**
- Export filtered applications as CSV for committee meetings
- Students check status at http://localhost:8000/application-status.html (roll + phone)
