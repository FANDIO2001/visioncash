# 💰 VisionCash - Personal Finance Management API

VisionCash is a comprehensive personal finance management platform built with Laravel 13 and modern web technologies. It provides a robust API for managing accounts, transactions, budgets, recurring payments, integrations, and subscription management.

## 🎯 Features

### Core Functionality
- **Account Management** - Multiple account types (bank, credit card, digital wallets)
- **Transaction Tracking** - Income, expenses, transfers with categorization
- **Budget Planning** - Set budgets by category with spend alerts
- **Recurring Transactions** - Automate regular payments and income
- **CSV Import** - Bulk import transactions from bank exports
- **Integrations** - Connect to external banking providers for syncing
- **Report Generation** - Export detailed financial reports

### User Features
- **Multi-Factor Authentication (MFA)** - Enhanced security
- **Notification System** - Email, SMS, push notifications with preferences
- **Subscription Management** - Handle SaaS subscriptions with invoicing
- **Payment Methods** - Manage multiple payment instruments
- **Session Management** - Secure user sessions and activity tracking
- **Customization** - User profiles, themes, language preferences

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Laravel 13.8, PHP 8.3+ |
| **Database** | MySQL 8+ / PostgreSQL (SQLite for dev) |
| **Authentication** | Laravel Sanctum (API tokens) |
| **Frontend** | Vite, TailwindCSS 4, Node.js |
| **Testing** | PHPUnit 12.5, Mockery |
| **Code Quality** | Pint (Laravel linter) |
| **Dev Tools** | Artisan CLI, Tinker, Pail |

---

## 📋 Quick Start

### Prerequisites
- PHP 8.3+
- Composer 2+
- Node.js 18+
- MySQL 8+ (or PostgreSQL)

### Installation

1. **Clone and setup**
```bash
git clone <repo-url> visioncash
cd visioncash
composer install
npm install
```

2. **Generate app key**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configure database**
```bash
# Edit .env with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=visioncash
DB_USERNAME=root
DB_PASSWORD=
```

4. **Run migrations**
```bash
php artisan migrate
```

5. **Seed sample data** (optional)
```bash
php artisan db:seed
```

6. **Start development server**
```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite frontend dev
npm run dev

# Terminal 3: Queue worker
php artisan queue:listen --tries=1

# Terminal 4: Logs
php artisan pail --timeout=0
```

Or use the convenient composer script:
```bash
composer run dev
```

---

## 🗄️ Database Models

### Authentication & Users
- **User** - Application users with profile info
- **MfaToken** - Multi-factor authentication tokens
- **UserSession** - Active user sessions
- **PasswordReset** - Password reset tokens

### Financial Accounts
- **Account** - Bank/payment accounts
- **AccountType** - Account type definitions (Savings, Checking, CC, etc)
- **AccountBalanceHistory** - Historical balance snapshots

### Transactions & Categories
- **Transaction** - Individual transactions (income/expense)
- **TransactionAttachment** - File attachments (receipts, invoices)
- **Category** - Transaction categories
- **RecurringTransaction** - Automated recurring transactions

### Budgets & Planning
- **Budget** - Budget allocations by category
- **BudgetHistory** - Historical budget tracking

### Integrations & Sync
- **Integration** - Bank provider connections
- **Provider** - Third-party provider definitions
- **SyncLog** - Sync operation logs
- **CsvImport** - CSV import records

### Notifications
- **Notification** - System notifications
- **NotificationChannel** - Notification delivery channels (email, SMS, push)
- **NotificationLog** - Notification delivery logs
- **NotificationPreference** - User notification preferences
- **UserNotification** - User notification history

### Subscriptions & Billing
- **Plan** - Subscription plans
- **Subscription** - User subscriptions
- **Invoice** - Invoices/billing documents
- **PaymentMethod** - Payment instruments
- **Coupon** - Discount coupons
- **CouponPlan** - Coupon-plan associations

### Reports
- **ReportExport** - Exported financial reports

---

## 🔌 API Endpoints

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
API requests require a Bearer token obtained via login. See [docs/API.md](docs/API.md) for complete documentation.

---

## 📁 Project Structure

```
visioncash/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API controllers
│   │   ├── Requests/             # Request validation
│   │   └── Resources/            # JSON resources
│   ├── Models/                   # 26 Eloquent models ✓
│   ├── Providers/                # Service providers
│   └── Services/                 # Business logic layer
├── config/                       # Configuration files ✓
├── database/
│   ├── migrations/               # Database migrations ✓
│   ├── factories/                # Model factories
│   └── seeders/                  # Database seeders
├── routes/
│   ├── api.php                   # API routes
│   ├── web.php                   # Web routes
│   └── console.php               # Artisan commands
├── resources/
│   ├── css/                      # Stylesheets
│   └── js/                       # Frontend JS
├── tests/                        # Test suite
├── docs/                         # Documentation
├── composer.json                 # PHP dependencies
├── package.json                  # Node dependencies
└── .env.example                  # Environment template
```

---

## 📚 Documentation

- **[API Documentation](docs/API.md)** - Complete API reference with examples
- **[Architecture Guide](docs/ARCHITECTURE.md)** - Code organization and patterns
- **[Setup Guide](docs/SETUP.md)** - Detailed installation instructions
- **[Development](docs/DEVELOPMENT.md)** - Coding standards and best practices

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run specific test
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage

# Watch mode
php artisan test --watch
```

---

## 🚀 Deployment

See [docs/SETUP.md](docs/SETUP.md) for production deployment checklist and instructions.

---

## 📄 License

MIT License - see LICENSE file for details

---

**Last Updated**: May 29, 2026  
**Version**: 0.1.0 (Early Development)

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
