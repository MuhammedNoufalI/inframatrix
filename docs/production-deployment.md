# Production Deployment Guide

This guide covers the necessary steps to harden and deploy InfraMatrix (Laravel 11 + Filament v3) in a production environment, specifically when running behind reverse proxies or SSL termination points.

## 1. System Requirements

- **PHP 8.2+**
- **Required Extensions:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML.
- **MySQL 8.0+**
- **Composer 2.x**

## 2. Web Server Configuration

### Webroot
The web server (Nginx, Apache, etc.) MUST have its document root set to the `/public` directory of the project.

### Nginx Configuration
Ensure your Vhost configuration includes the standard Laravel routing rule:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Reverse Proxy / SSL Termination
If you are running behind a proxy (like Varnish, Cloudflare, or a Load Balancer):

1. **Forwarded Headers:** Ensure your proxy passes the following headers:
   - `X-Forwarded-For`
   - `X-Forwarded-Proto` (essential for HTTPS detection)
   - `X-Forwarded-Host`
   - `X-Forwarded-Port`

2. **Varnish / Caching:** Admin panels (Filament) and Livewire requests should generally bypass caching to prevent state issues.
   - Exclude paths: `/admin/*`, `/filament/*`, `/livewire/*`.

## 3. Environment Configuration (.env)

Harden your production `.env` with these settings:

```ini
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# Trusted Proxies (Required for SSL detection behind proxies)
# Set to '*' to trust all proxies (typical for hosting panels)
TRUSTED_PROXIES=*

# Secure Cookies
SESSION_SECURE_COOKIE=true
```

## 4. Deployment Commands

Run these commands on every deployment to ensure the application is optimized:

```bash
# Clear all caches (useful during major updates)
php artisan optimize:clear

# Cache configuration, routes, and views for speed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Filament components
php artisan filament:cache-components

# Ensure storage is linked
php artisan storage:link
```

## 5. Security Best Practices

- **APP_KEY:** Ensure a unique encryption key is generated (`php artisan key:generate`).
- **Filesystem:** Ensure the `storage` and `bootstrap/cache` directories are writable by the web server user.
- **Permissions:** Never use `777` permissions; use `775` for directories and `644` for files, owned by the web user.
