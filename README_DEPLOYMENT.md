# Render Deployment

## Overview
This repository is configured for deployment on Render using Docker.

## Added files
- `Dockerfile` — builds PHP 8.2 Apache container and installs `pdo_mysql`.
- `render.yaml` — Render service definition for the web service.
- `config/database.php` — updated to read DB credentials from environment variables.

## Required Render environment variables
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_CHARSET` (optional, defaults to `utf8mb4`)
- `APP_BASE_URL` (recommended for correct asset and link generation)

## Render setup steps
1. Push this repo to GitHub.
2. In Render, create a new Web Service.
3. Choose `Docker` as the environment.
4. Use `render.yaml` and connect to your `main` branch.
5. Add environment variables in Render Dashboard.
6. Deploy.

## Database migration
`render.yaml` includes a `releaseCommand` to apply `database/migration_customer.sql` automatically during deployment.

If you need a managed MySQL database, create it in Render and use those credentials.

## Notes
- If `APP_BASE_URL` is set, it will be used for `getBaseUrl()` across the app.
- If not set, the app still resolves the base URL from the request host.
