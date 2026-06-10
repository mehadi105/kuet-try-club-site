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
| DB_USER   | your macOS user | DB username    |
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

```bash
php -S localhost:8000
```

Then open:

- Home: http://localhost:8000/index.html
- Join form: http://localhost:8000/join.html
- API health: http://localhost:8000/api/health.php

## Join form API

- **URL:** `POST /api/submit-join.php`
- **Body:** `multipart/form-data` (same fields as the HTML form)
- **Response:** JSON `{ "success": true|false, "message": "..." }`

## View saved applications (PostgreSQL)

```bash
psql -d try_kuet -c "SELECT id, fullname, roll, department, created_at FROM join_applications;"
```
