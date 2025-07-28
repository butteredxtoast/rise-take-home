# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel API application that serves US Presidential data from Google BigQuery. The application provides two main endpoints for querying presidential information and generating statistical insights.

## Architecture

- **Framework**: Laravel 12 with PHP 8.2+
- **Data Source**: Google Cloud BigQuery (`rise-take-home-463523.us_presidents.presidents`)
- **Deployment**: Google Cloud Run with Docker containerization
- **Development Environment**: Laravel Sail (Docker-based)

### Key Components

- **PresidentsController** (`app/Http/Controllers/PresidentsController.php`): Handles API requests and validation
- **PresidentsService** (`app/Services/PresidentsService.php`): Contains BigQuery integration and business logic
- **API Routes** (`routes/api.php`): Defines endpoints for presidential data queries

## API Endpoints

- `GET /api/health` - Health check endpoint
- `GET /api/presidents/{mm-dd-yyyy}` - Returns president in office on given date with term length
- `GET /api/random` - Returns fun statistical facts about presidents (astrological signs, birth/death day patterns)

## Development Commands

### Testing
```bash
# Run all tests
php artisan test

# Run tests in Docker (Sail)
./vendor/bin/sail test
```

### Code Quality
```bash
# Run Pint (Laravel's code formatter)
php artisan pint

# Run linting via Pint
./vendor/bin/pint
```

### Local Development
```bash
# Start development server with Sail (includes MySQL, queue worker, logs, and Vite)
composer run dev

# Start just the Laravel server
php artisan serve

# Start Sail environment
./vendor/bin/sail up -d
```

### Build & Deploy
```bash
# Build frontend assets
npm run build

# Generate application key
php artisan key:generate

# Clear application caches
php artisan config:clear
php artisan cache:clear
```

## BigQuery Integration

The application connects to BigQuery using the Google Cloud BigQuery PHP client. Authentication is handled via:
- `GOOGLE_APPLICATION_CREDENTIALS` environment variable (service account key file path)
- Project ID: `rise-take-home-463523`

Date columns in BigQuery are stored as strings and must be parsed using Carbon with format `'F j, Y'` (e.g., "January 20, 2009").

## Development Setup

1. Requires Google Cloud credentials mounted at `~/.config/gcloud` or via `GOOGLE_APPLICATION_CREDENTIALS`
2. Uses SQLite for local database (`database/database.sqlite`)
3. Sail provides containerized development environment with MySQL
4. Procfile exists for Cloud Run deployment (`php artisan serve --host=0.0.0.0 --port=$PORT`)

## Data Quirks

- Presidents with ongoing terms have "Present" in `Term End` field
- Deceased presidents have specific death dates, living presidents show "Still alive"
- Date parsing requires handling both "Present" and actual date strings
- Grover Cleveland served non-consecutive terms (data includes both)