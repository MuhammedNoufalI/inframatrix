# Production Deployment & Reverse Proxy Guide

This document provides instructions for deploying InfraMatrix (Laravel 11 + Filament v3) in professionally managed environments, particularly behind reverse proxies (Nginx, Varnish, Load Balancers).

## 1. Prerequisites

- **PHP 8.2+** (Extensions: `bcmath, ctype, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml`)
- **MariaDB 10.11+** or **MySQL 8.0+**
- **Composer 2.x**
- **Hosting Panel:** Compatible with CloudPanel, Plesk, cPanel, or custom Nginx setups.

## 2. Web Server Configuration

### Document Root
The web server's **Document Root** MUST point to the `/public` directory of the repository.

### Nginx Vhost Requirements
Ensure the following rule is present to handle Laravel's routing:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Reverse Proxy & SSL Termination
If your application terminates SSL at a proxy (e.g., CloudPanel's Varnish or a hardware load balancer):

1. **Proxy Headers:** Ensure the proxy transmits these headers:
   - `X-Forwarded-For`
   - `X-Forwarded-Proto` (Must be `https`)
   - `X-Forwarded-Host`

2. **Varnish Exclusions:** Prevent Varnish from caching dynamic administrative routes:
   - Exclude: `/admin/*`, `/livewire/*`, `/filament/*`.

## 3. Application Hardening (.env)

Configure your production `.env` with these hardened settings:

```ini
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# Trust proxy headers for correct SSL/IP detection
TRUSTED_PROXIES=*

# Secure session cookies (Enforce HTTPS)
SESSION_SECURE_COOKIE=true
```

## 4. Deployment Pipeline

Run these commands during deployment to refresh the environment and optimize performance:

```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Update database
php artisan migrate --force

# Optimize & Cache
php artisan optimize
php artisan view:cache

# Publish Frontend Assets (CRITICAL for Nginx/CloudPanel to avoid 404s on .js files)
php artisan livewire:publish --assets
php artisan filament:assets
php artisan filament:cache-components

# Linked Storage
php artisan storage:link
```

## 5. Troubleshooting (405 Method Not Allowed)

If you encounter a `405 Method Not Allowed` error on login:
1. Ensure `APP_URL` in `.env` starts with `https://`.
2. Ensure `TRUSTED_PROXIES=*` is set in `.env`.
3. Verify that the web server passes the `X-Forwarded-Proto` header.
4. Run `php artisan optimize:clear` to ensure no old route/config caches are lingering.
