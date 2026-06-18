# Setup & Installation Guide - VisionCash

Complete step-by-step guide to set up VisionCash for development and production environments.

## Table of Contents

1. [Development Setup](#development-setup)
2. [Production Deployment](#production-deployment)
3. [Database Setup](#database-setup)
4. [Troubleshooting](#troubleshooting)

---

## Development Setup

### System Requirements

- **PHP 8.3+** with extensions:
    - `php-sqlite3` or `php-mysql`
    - `php-json`
    - `php-curl`
    - `php-xml`
    - `php-mbstring`
    - `php-zip`
    - `php-bcmath`

- **Node.js 18+** with npm

- **Composer 2.0+** ([Install](https://getcomposer.org/download/))

- **MySQL 8+** or **PostgreSQL 12+** (optional, SQLite works for development)

### Step 1: Clone Repository

```bash
git clone https://github.com/your-org/visioncash.git
cd visioncash
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

If you encounter issues, try:

```bash
composer install --no-interaction --prefer-dist
```

### Step 3: Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```env
APP_NAME="VisionCash"
APP_DEBUG=true
DB_CONNECTION=sqlite
# Or MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=visioncash
# DB_USERNAME=root
# DB_PASSWORD=
```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

This creates a unique encryption key needed by Laravel.

### Step 5: Install Node Dependencies

```bash
npm install
```

### Step 6: Run Database Migrations

Create the database first (MySQL only):

```bash
mysql -u root -p -e "CREATE DATABASE visioncash CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then run migrations:

```bash
php artisan migrate
```

You should see:

```
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table (123.45ms)
...
```

### Step 7: Seed Sample Data (Optional)

```bash
php artisan db:seed
```

This populates the database with sample users, accounts, and transactions for testing.

### Step 8: Run Development Server

Use the all-in-one command:

```bash
composer run dev
```

This starts:

- **Laravel server** on `http://localhost:8000`
- **Vite dev server** for frontend
- **Queue worker** for background jobs
- **Laravel Pail** for log monitoring

Or run separately:

**Terminal 1 - Laravel Backend:**

```bash
php artisan serve
```

Access at `http://localhost:8000`

**Terminal 2 - Frontend Development:**

```bash
npm run dev
```

Vite will be available at `http://localhost:5173`

**Terminal 3 - Queue Worker:**

```bash
php artisan queue:listen --tries=1 --timeout=0
```

**Terminal 4 - Log Viewer:**

```bash
php artisan pail --timeout=0
```

### Step 9: Verify Installation

Test the API:

```bash
curl http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Production Deployment

### Pre-Deployment Checklist

- [ ] Update `.env` with production values
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure MySQL/PostgreSQL database
- [ ] Set up Redis for cache/sessions (recommended)
- [ ] Configure email service (SMTP or SendGrid)
- [ ] Set up storage (local or AWS S3)
- [ ] Configure SSL/HTTPS certificate
- [ ] Set up domain name and DNS

### Deployment Steps

#### 1. Set Up Web Server (Nginx/Apache)

**Nginx Configuration Example:**

```nginx
server {
    listen 80;
    server_name api.visioncash.com;
    root /var/www/visioncash/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 2. Clone and Setup

```bash
cd /var/www
git clone https://github.com/your-org/visioncash.git visioncash
cd visioncash

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

#### 3. Configure Environment

```bash
cp .env.example .env
# Edit .env with production values
nano .env
```

Production `.env` example:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx
APP_URL=https://api.visioncash.com

DB_CONNECTION=mysql
DB_HOST=db.example.com
DB_DATABASE=visioncash_prod
DB_USERNAME=dbuser
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
REDIS_HOST=redis.example.com

QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=sg_...

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=xxxxx
AWS_SECRET_ACCESS_KEY=xxxxx
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=visioncash-prod
```

#### 4. Generate Application Key

```bash
php artisan key:generate --force
```

#### 5. Create Database

```bash
mysql -h db.example.com -u root -p -e "CREATE DATABASE visioncash_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### 6. Run Migrations

```bash
php artisan migrate --force
```

The `--force` flag confirms in production.

#### 7. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### 8. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/visioncash
sudo chmod -R 775 storage bootstrap/cache
```

#### 9. Setup Queue Worker

Create `/etc/supervisor/conf.d/visioncash-worker.conf`:

```ini
[program:visioncash-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/visioncash/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/visioncash-worker.log
```

Start:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start visioncash-worker:*
```

#### 10. Setup Scheduled Tasks

Add to crontab:

```bash
sudo crontab -e
```

Add:

```
* * * * * cd /var/www/visioncash && php artisan schedule:run >> /dev/null 2>&1
```

#### 11. Setup SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d api.visioncash.com
```

Update Nginx config to use SSL.

---

## Database Setup

### MySQL Setup

1. **Create User:**

```sql
CREATE USER 'visioncash'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON visioncash.* TO 'visioncash'@'localhost';
FLUSH PRIVILEGES;
```

2. **Create Database:**

```sql
CREATE DATABASE visioncash CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Verify:**

```bash
mysql -u visioncash -p visioncash -e "SELECT 1;"
```

### PostgreSQL Setup

```bash
sudo -u postgres createdb -E UTF8 -T template0 visioncash
sudo -u postgres createuser visioncash
sudo -u postgres psql << EOF
ALTER USER visioncash WITH PASSWORD 'strong_password';
GRANT ALL PRIVILEGES ON DATABASE visioncash TO visioncash;
EOF
```

### Run Migrations

```bash
# Fresh database
php artisan migrate:fresh --seed

# Add new migration
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset

# Check status
php artisan migrate:status
```

---

## Troubleshooting

### Composer Issues

**Problem:** `composer install` fails

**Solutions:**

```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Dump autoloader
composer dump-autoload

# Try with --no-interaction
composer install --no-interaction --prefer-dist
```

### Permission Issues

**Problem:** Storage/Bootstrap directory not writable

**Solution:**

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R $(whoami) storage bootstrap/cache

# macOS with Homestead
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Issues

**Problem:** `SQLSTATE[HY000]: General error: 14 unable to open database file`

**Solution:** Ensure `database` directory exists:

```bash
mkdir -p database
touch database/database.sqlite
php artisan migrate
```

**Problem:** MySQL connection refused

**Solutions:**

1. Check MySQL is running: `mysql -u root -p`
2. Verify credentials in `.env`
3. Ensure database exists: `mysql -u root -p -e "CREATE DATABASE visioncash;"`

### Build Issues

**Problem:** `npm run build` fails

**Solution:**

```bash
# Clear node_modules
rm -rf node_modules package-lock.json

# Reinstall
npm install

# Build
npm run build
```

### Key Generation Issues

**Problem:** `php artisan key:generate` fails

**Solution:**

```bash
# Generate manually
php artisan key:generate --show

# Copy the output and set in .env
APP_KEY=base64:xxxxx
```

### Route Caching Issues

**Problem:** Routes not working after deployment

**Solution:**

```bash
# Clear route cache
php artisan route:clear

# Recache
php artisan route:cache
```

---

## Health Check

After deployment, verify everything works:

```bash
# Database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Queue connection
php artisan queue:work --daemon

# Storage
php artisan storage:link

# Migrate
php artisan migrate --pretend
```

---

## Next Steps

- [API Documentation](API.md)
- [Architecture Guide](ARCHITECTURE.md)
- [Development Guide](DEVELOPMENT.md)

For help, consult [Laravel Documentation](https://laravel.com/docs) or create an issue on GitHub.
