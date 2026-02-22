# Deployment Guide: InfraMatrix

This guide covers deploying the application to a Linux server using **CloudPanel** or **Plesk**.

## 1. Server Prerequisites
- **PHP 8.2+** (with extensions: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml)
- **MySQL 8.0+**
- **Composer 2.x**
- **Node.js & NPM** (for assets)

---

## 2. Platform & Database Setup (CloudPanel / Plesk / Hostinger)
Before touching the terminal, set up your hosting environment:

1. **Create the Web Domain**:
   - In your panel (e.g. CloudPanel), create a new PHP site/domain.
   - **Crucial**: Set the Document Root to the `<project-folder>/public` directory.

2. **Create a Fresh Database**:
   - Navigate to the Databases section of your hosting panel.
   - Create a new **MySQL Database** (e.g., `tw_inframatrix_prod`).
   - Create a **Database User** and generate a strong password.
   - Assign the user to the database with all privileges.
   - *Note down the Database Name, Username, and Password.*

---

## 3. Clone and Install
SSH into your server and navigate to your web root (e.g., `/home/cloudpanel/htdocs/your-domain.com/`):

```bash
# Clone the repository
git clone <your-repo-url> .

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build assets
npm install
npm run build
```

---

## 4. Environment Configuration
For a clean deployment without committing secrets to GitHub, use the provided `.env.example` template:

```bash
cp .env.example .env
php artisan key:generate
```

Edit the newly renamed `.env` file (`nano .env`) with your production details:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Connection (Enter the details from Step 2)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# SendGrid Mail Configuration (Required for User Invites)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="admin@yourdomain.com"
MAIL_FROM_NAME="InfraMatrix"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## 5. Finalize Deployment (Fresh Database)
Run the migrations to build the tables and clear the cache. Since this is a fresh database, we will run the initial seeder as well to populate default roles and an admin user.

```bash
# Build database tables and seed Initial Admin + Roles
php artisan migrate --force --seed

# Optimize for production
php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache

# Create storage link for file uploads
php artisan storage:link
```

---

## 6. Web Server Configuration
### CloudPanel / Plesk (Nginx)
Ensure your **Document Root** is set to the `/public` directory of the project.

### File Permissions
Ensure the `storage` and `bootstrap/cache` directories are writable by the web server user:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 7. Admin Access
Once deployed, log in to your admin panel at:
`https://your-domain.com/admin`

**Default Credentials:**
- **Email:** `admin@timesworld.com`
- **Password:** `password` (Change this immediately after login!)
