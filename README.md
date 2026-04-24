# Leads Management — PHP Backend

A RESTful API built with **Slim 4**, **PDO/PostgreSQL**, and **JWT authentication**.

## Requirements

- PHP 8.1+
- Composer
- PostgreSQL 14+

## Setup

```bash
# 1. Install dependencies
composer install

# 2. Copy and configure environment
cp .env.example .env
# Edit .env with your DB credentials

# 3. Run the schema against your Postgres DB
psql -U postgres -d leadsdb -f /scripts/schema.sql
psql -U postgres -d leadsdb -f /scripts/data.sql

# 4. Start the dev server
composer start        # listens on http://localhost:8081
```

## Docker

```bash
docker compose up php-api postgres
```

## Running Tests

```bash
composer test
```
