# Database Models & Schema - VisionCash

Complete reference of all 26 Eloquent models and their relationships.

## Model Hierarchy

```
┌─────────────────────────────────────────────────────────┐
│                      USER (Hub)                         │
│  Connects to: 13 models (center of entire system)      │
└─────────────────────────────────────────────────────────┘
        │
        ├── AUTHENTICATION
        │   ├── MfaToken (Multi-factor auth)
        │   ├── UserSession (Active sessions)
        │   └── PasswordReset (Password resets)
        │
        ├── FINANCIAL ACCOUNTS
        │   ├── Account (Multiple accounts per user)
        │   │   ├── AccountType (Account classification)
        │   │   ├── AccountBalanceHistory (Balance tracking)
        │   │   └── Integration (Bank connections)
        │   │
        │   └── Category (Transaction categories)
        │
        ├── TRANSACTIONS
        │   ├── Transaction (Individual transactions)
        │   │   └── TransactionAttachment (Receipts, docs)
        │   └── RecurringTransaction (Automated payments)
        │
        ├── BUDGETS
        │   └── Budget (Category budgets)
        │       └── BudgetHistory (Budget tracking)
        │
        ├── NOTIFICATIONS
        │   ├── Notification (System notifications)
        │   ├── NotificationChannel (Email, SMS, push)
        │   ├── NotificationLog (Delivery logs)
        │   ├── NotificationPreference (User preferences)
        │   └── UserNotification (Notification history)
        │
        ├── INTEGRATIONS
        │   ├── Integration (Provider connections)
        │   │   ├── Provider (Provider definitions)
        │   │   └── SyncLog (Sync operation logs)
        │   └── CsvImport (Bulk imports)
        │
        ├── SUBSCRIPTIONS & BILLING
        │   ├── Plan (Subscription plans)
        │   ├── Subscription (User subscriptions)
        │   ├── Invoice (Billing documents)
        │   ├── PaymentMethod (Payment instruments)
        │   ├── Coupon (Discount coupons)
        │   └── CouponPlan (Coupon-plan linking)
        │
        └── REPORTS
            └── ReportExport (Generated reports)
```

---

## Models Reference

### 1. User
**Purpose**: Application user with full profile

**Fields**:
- `id` (PK)
- `first_name`, `last_name`
- `email` (unique)
- `email_verified_at`
- `password` (hashed)
- `default_currency` (USD, EUR, etc)
- `language` (en, fr, etc)
- `timezone`
- `is_active` (boolean)
- `last_login_at`
- `avatar_url`, `bio`
- `date_of_birth`, `country`, `city`
- `theme` (light/dark)
- `notifications_enabled` (boolean)
- `email_notifications`, `push_notifications`, `sms_notifications` (boolean)
- `digest_frequency` (daily, weekly, etc)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
hasMany: MfaToken, Account, Category, Transaction, RecurringTransaction, 
         Budget, Integration, CsvImport, ReportExport, NotificationChannel, 
         Notification, Subscription, Invoice, PaymentMethod, UserSession
hasOne: NotificationPreference
```

**Scopes**: `active()`

---

### 2. MfaToken
**Purpose**: Store multi-factor authentication tokens

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `token` (unique, hashed)
- `type` (totp, sms, email)
- `secret` (for TOTP)
- `verified` (boolean)
- `expires_at`
- `created_at`

**Relations**:
```php
belongsTo: User
```

---

### 3. UserSession
**Purpose**: Track active user sessions for security

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `ip_address`
- `user_agent`
- `last_activity_at`
- `created_at`, `updated_at`

**Relations**:
```php
belongsTo: User
```

---

### 4. PasswordReset
**Purpose**: Store password reset tokens

**Fields**:
- `email` (PK)
- `token` (hashed)
- `created_at`

**Relations**: None (utility table)

---

### 5. Account
**Purpose**: User bank/payment account (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK) - NOT NULL
- `account_type_id` (FK) - NOT NULL
- `integration_id` (FK) - nullable
- `account_number` (unique per account_type)
- `account_name`
- `is_active` (boolean, indexed)
- `currency` (USD, EUR, etc)
- `color` (hex for UI)
- `iban` (optional)
- `initial_balance` (decimal:2)
- `balance` (decimal:2, computed from transactions)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, AccountType, Integration (withDefault)
hasMany: Transaction, AccountBalanceHistory, RecurringTransaction, CsvImport
```

**Scopes**:
```php
active()                    # is_active = true
byUser($userId)            # user_id = $userId
overdrawn()                # balance < 0
```

**Accessors**:
```php
is_overdrawn               # boolean: balance < 0
formatted_balance          # "1000.50 USD"
```

---

### 6. AccountType
**Purpose**: Classify account types (Savings, Checking, Credit Card, etc)

**Fields**:
- `id` (PK)
- `name` (Savings, Checking, Credit Card, Wallet, etc)
- `description`
- `created_at`, `updated_at`

**Relations**:
```php
hasMany: Account
```

---

### 7. AccountBalanceHistory
**Purpose**: Track historical account balances for reporting

**Fields**:
- `id` (PK)
- `account_id` (FK)
- `balance` (decimal:2)
- `recorded_at` (timestamp of snapshot)
- `created_at`

**Relations**:
```php
belongsTo: Account
```

---

### 8. Category
**Purpose**: Transaction categories (Groceries, Gas, Salary, etc)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `name` (Groceries, Entertainment, Salary, etc)
- `type` (income, expense)
- `icon` (emoji or icon name)
- `color` (hex)
- `is_default` (boolean - system categories)
- `is_active` (boolean, indexed)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User
hasMany: Transaction, Budget, RecurringTransaction
```

**Scopes**:
```php
active()                   # is_active = true
defaults()                 # is_default = true
income()                   # type = 'income'
expense()                  # type = 'expense'
byUser($userId)           # user_id = $userId
```

---

### 9. Transaction
**Purpose**: Individual income/expense transaction (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `account_id` (FK)
- `category_id` (FK)
- `related_transaction_id` (FK, nullable - for transfers/splits)
- `amount` (decimal:2)
- `transaction_type` (income, expense, transfer)
- `description` (merchant name, note)
- `currency` (USD, EUR, etc)
- `is_manual` (boolean - user-entered vs synced)
- `is_read_only` (boolean - from bank integration)
- `external_reference` (bank transaction ID)
- `attachment_url` (receipt, invoice)
- `created_by_source` (manual, plaid, csv, etc)
- `transaction_date` (date of transaction)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Account, Category, Transaction (relatedTransaction)
hasMany: TransactionAttachment
```

**Scopes**:
```php
income()                   # transaction_type = 'income'
expense()                  # transaction_type = 'expense'
betweenDates($from, $to)  # transaction_date between dates
recent($days = 30)        # last N days
byUser($userId)           # user_id = $userId
```

**Indexes**:
- `(user_id, account_id, transaction_date)`
- `(account_id, is_read_only)`

---

### 10. TransactionAttachment
**Purpose**: Store receipts, invoices, documents attached to transactions

**Fields**:
- `id` (PK)
- `transaction_id` (FK)
- `file_path` (S3 path or local)
- `file_name` (original name)
- `file_size` (bytes)
- `mime_type` (application/pdf, image/jpeg, etc)
- `created_at`

**Relations**:
```php
belongsTo: Transaction
```

---

### 11. RecurringTransaction
**Purpose**: Automated transactions (salary every 1st, rent every month, etc) (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `account_id` (FK)
- `category_id` (FK)
- `name` (Salary, Rent, Netflix Subscription, etc)
- `amount` (decimal:2)
- `frequency` (daily, weekly, biweekly, monthly, yearly)
- `frequency_interval` (every N days/weeks/etc)
- `is_active` (boolean)
- `next_due_date` (date)
- `last_executed_at` (timestamp)
- `ends_at` (nullable - when to stop)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Account, Category
```

**Scopes**:
```php
active()                   # is_active = true
dueSoon($days = 7)        # next_due_date <= now + N days
expired()                 # ends_at < now
byFrequency($freq)        # frequency = $freq
byUser($userId)           # user_id = $userId
```

**Accessors**:
```php
is_due_today               # boolean
is_expired                 # boolean
days_until_next            # int
```

---

### 12. Budget
**Purpose**: Set spending limits by category (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `category_id` (FK)
- `amount` (decimal:2) - budget limit
- `spent` (decimal:2) - current spend
- `period_type` (weekly, monthly, yearly)
- `start_date` (date)
- `end_date` (date, nullable)
- `alert_threshold_percentage` (80)
- `alert_sent_80` (boolean - 80% alert sent)
- `alert_sent_100` (boolean - 100% alert sent)
- `is_active` (boolean, indexed)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Category
hasMany: BudgetHistory
```

**Scopes**:
```php
active()                   # is_active = true
currentPeriod()           # start_date <= now <= end_date
byUser($userId)           # user_id = $userId
exceededThreshold()       # spent >= amount * threshold%
```

**Accessors**:
```php
percentage_spent           # float: (spent / amount) * 100
is_exceeded                # boolean: spent >= amount
remaining                  # decimal: amount - spent
```

---

### 13. BudgetHistory
**Purpose**: Track budget spend over time for trends

**Fields**:
- `id` (PK)
- `budget_id` (FK)
- `spent_amount` (decimal:2)
- `recorded_at` (timestamp)
- `created_at`

**Relations**:
```php
belongsTo: Budget
```

---

### 14. Provider
**Purpose**: Define third-party providers (Plaid, Yodlee, etc)

**Fields**:
- `id` (PK)
- `name` (Plaid, Yodlee, Open Banking API, etc)
- `slug` (plaid, yodlee - for code reference)
- `base_url` (API endpoint)
- `api_key` (provider's key)
- `is_active` (boolean)
- `created_at`, `updated_at`

**Relations**:
```php
hasMany: Integration
```

---

### 15. Integration
**Purpose**: User's connection to banking provider (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `provider_id` (FK)
- `account_id` (FK, nullable)
- `access_token` (encrypted)
- `refresh_token` (encrypted, nullable)
- `is_active` (boolean, indexed)
- `last_synced_at` (timestamp)
- `next_sync_at` (timestamp)
- `last_error_message` (string, nullable)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Provider, Account (withDefault)
hasMany: SyncLog
```

**Scopes**:
```php
active()                   # is_active = true
byProvider($id)           # provider_id = $id
byUser($userId)           # user_id = $userId
needingSync()             # next_sync_at <= now
inError()                 # last_error_message NOT NULL
```

**Accessors**:
```php
is_token_expired           # boolean
seconds_until_next_sync    # int
```

---

### 16. SyncLog
**Purpose**: Track bank synchronization operations

**Fields**:
- `id` (PK)
- `integration_id` (FK)
- `status` (pending, processing, success, failed)
- `started_at` (timestamp)
- `ended_at` (timestamp)
- `transactions_imported` (int)
- `transactions_failed` (int)
- `error_message` (string, nullable)
- `created_at`

**Relations**:
```php
belongsTo: Integration
```

**Scopes**:
```php
successful()              # status = 'success'
failed()                  # status = 'failed'
inProgress()              # status = 'processing'
recent($days = 30)        # created_at >= now - N days
```

**Indexes**:
- `(integration_id, status)`

**Accessors**:
```php
duration                   # seconds: ended_at - started_at
success_rate               # percentage: success / total
```

---

### 17. CsvImport
**Purpose**: Track CSV import operations (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `account_id` (FK)
- `file_name`
- `file_path` (S3 path)
- `status` (pending, processing, completed, failed)
- `total_rows` (int)
- `imported_count` (int)
- `failed_count` (int)
- `error_details` (JSON)
- `started_at`, `completed_at` (timestamps)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Account
```

**Scopes**:
```php
completed()               # status = 'completed'
failed()                  # status = 'failed'
pending()                 # status = 'pending'
processing()              # status = 'processing'
```

**Accessors**:
```php
success_rate              # percentage: imported_count / total_rows
failure_rate              # percentage: failed_count / total_rows
```

---

### 18. Notification
**Purpose**: System notifications (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `title`
- `message` (content)
- `type` (info, warning, error, success)
- `read_at` (timestamp, nullable)
- `action_url` (nullable - link in notification)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User
hasMany: NotificationLog
```

**Scopes**:
```php
unread()                  # read_at IS NULL
read()                    # read_at IS NOT NULL
byType($type)             # type = $type
recent($days = 30)        # created_at >= now - N days
forUser($userId)          # user_id = $userId
```

**Accessors**:
```php
is_read                    # boolean: read_at !== null
```

**Methods**:
```php
markAsRead()              # Set read_at = now
markAsUnread()            # Set read_at = NULL
```

**Indexes**:
- `(user_id, read_at)`

---

### 19. NotificationChannel
**Purpose**: Delivery channels (Email, SMS, Push)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `name` (Email, SMS, Push Notification)
- `type` (email, sms, push)
- `address` (email address, phone number, device token)
- `is_verified` (boolean)
- `verified_at` (timestamp)
- `is_active` (boolean)
- `created_at`, `updated_at`

**Relations**:
```php
belongsTo: User
hasMany: NotificationLog
hasOne: NotificationPreference
```

---

### 20. NotificationLog
**Purpose**: Track notification delivery success/failure

**Fields**:
- `id` (PK)
- `notification_id` (FK)
- `channel_id` (FK)
- `status` (sent, failed, bounced, read)
- `sent_at` (timestamp)
- `error_message` (string, nullable)
- `created_at`

**Relations**:
```php
belongsTo: Notification, NotificationChannel
```

---

### 21. NotificationPreference
**Purpose**: User's notification delivery preferences

**Fields**:
- `id` (PK)
- `user_id` (FK, unique)
- `channel_id` (FK, unique per user)
- `budget_alerts` (boolean)
- `transaction_alerts` (boolean)
- `subscription_reminders` (boolean)
- `security_alerts` (boolean)
- `marketing` (boolean)
- `frequency` (instant, daily, weekly)
- `quiet_hours_start` (time)
- `quiet_hours_end` (time)
- `created_at`, `updated_at`

**Relations**:
```php
belongsTo: User, NotificationChannel
```

---

### 22. UserNotification
**Purpose**: User notification history/inbox

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `notification_id` (FK)
- `read` (boolean)
- `archived` (boolean)
- `created_at`

**Relations**:
```php
belongsTo: User
```

---

### 23. Plan
**Purpose**: Subscription plans (Pro $9.99/mo, Enterprise $99/mo, etc)

**Fields**:
- `id` (PK)
- `name` (Basic, Pro, Enterprise)
- `slug` (basic, pro, enterprise)
- `description`
- `price` (decimal:2)
- `currency` (USD, EUR)
- `billing_period` (monthly, yearly)
- `features` (JSON array of features)
- `max_accounts` (int)
- `max_budgets` (int)
- `is_active` (boolean)
- `created_at`, `updated_at`

**Relations**:
```php
hasMany: Subscription
belongsToMany: Coupon (via CouponPlan)
```

---

### 24. Subscription
**Purpose**: User subscription to a plan (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `plan_id` (FK)
- `status` (active, trial, past_due, expired, cancelled)
- `current_period_start` (date)
- `current_period_end` (date)
- `trial_ends_at` (date, nullable)
- `ended_at` (date, nullable)
- `auto_renew` (boolean)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Plan
hasMany: Invoice, PaymentMethod
```

**Scopes**:
```php
active()                  # status = 'active'
onTrial()                # status = 'trial'
pastDue()                # status = 'past_due'
expired()                # status = 'expired'
byUser($userId)          # user_id = $userId
```

**Accessors**:
```php
is_active                 # boolean: status = 'active'
is_on_trial               # boolean: status = 'trial'
days_remaining_in_trial   # int
days_until_renewal        # int
```

---

### 25. Invoice
**Purpose**: Billing documents for subscriptions (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `subscription_id` (FK)
- `coupon_id` (FK, nullable)
- `invoice_number` (string, unique)
- `status` (draft, sent, paid, overdue, cancelled)
- `amount` (decimal:2)
- `discount_amount` (decimal:2)
- `tax_amount` (decimal:2)
- `total_amount` (decimal:2)
- `currency` (USD, EUR)
- `invoice_date` (date)
- `due_date` (date)
- `paid_at` (date, nullable)
- `payment_method` (string, nullable)
- `external_invoice_id` (nullable - from Stripe, etc)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User, Subscription, Coupon (withDefault)
```

**Scopes**:
```php
paid()                    # status = 'paid'
pending()                 # status IN ('draft', 'sent', 'overdue')
byUser($userId)           # user_id = $userId
betweenDates($from, $to) # invoice_date between dates
overdue()                 # due_date < now AND status != 'paid'
```

**Indexes**:
- `(subscription_id, status)`
- `(invoice_date)`

**Accessors**:
```php
formatted_total           # "950.00 USD" (after discount)
is_paid                   # boolean: status = 'paid'
is_overdue                # boolean: due_date < now
```

---

### 26. PaymentMethod
**Purpose**: Store payment methods (Stripe token, PayPal, etc)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `subscription_id` (FK)
- `type` (credit_card, debit_card, paypal, etc)
- `card_last_four` (****1234)
- `card_brand` (Visa, Mastercard, Amex)
- `exp_month`, `exp_year` (int)
- `token` (encrypted - Stripe token, etc)
- `is_default` (boolean)
- `is_active` (boolean)
- `created_at`, `updated_at`

**Relations**:
```php
belongsTo: User, Subscription
```

---

### 27. Coupon
**Purpose**: Discount coupons (SAVE20 = 20% off, NEWUSER = free month)

**Fields**:
- `id` (PK)
- `code` (SAVE20, unique)
- `discount_type` (percentage, fixed)
- `discount_value` (decimal)
- `max_uses` (int, nullable - unlimited if null)
- `uses_count` (int)
- `valid_from` (date)
- `valid_until` (date, nullable)
- `is_active` (boolean)
- `created_at`, `updated_at`

**Relations**:
```php
belongsToMany: Plan (via CouponPlan)
hasMany: Invoice
```

---

### 28. CouponPlan
**Purpose**: Pivot table linking coupons to plans

**Fields**:
- `id` (PK)
- `coupon_id` (FK)
- `plan_id` (FK)
- `created_at`

**Relations**:
```php
belongsTo: Coupon, Plan
```

---

### 29. ReportExport
**Purpose**: Generated financial reports (soft deleted)

**Fields**:
- `id` (PK)
- `user_id` (FK)
- `report_type` (transactions, accounts, budgets)
- `format` (pdf, csv, excel)
- `status` (pending, processing, completed, failed)
- `start_date`, `end_date` (date range)
- `file_path` (S3 path, nullable)
- `file_name`
- `file_size` (bytes)
- `generated_at` (timestamp)
- `expires_at` (timestamp - when to delete)
- `created_at`, `updated_at`, `deleted_at`

**Relations**:
```php
belongsTo: User
```

**Scopes**:
```php
completed()               # status = 'completed'
failed()                  # status = 'failed'
processing()              # status = 'processing'
pending()                 # status = 'pending'
byUser($userId)           # user_id = $userId
recent($days = 30)        # created_at >= now - N days
```

**Accessors**:
```php
is_ready                   # boolean: status = 'completed' && file exists
```

---

## Schema Statistics

| Category | Count | Total Fields |
|----------|-------|-------------|
| Authentication | 3 | 15 |
| Accounts | 3 | 30 |
| Transactions | 2 | 25 |
| Budgets | 2 | 15 |
| Notifications | 5 | 35 |
| Integrations | 3 | 25 |
| Subscriptions & Billing | 6 | 50 |
| Reports | 1 | 15 |
| Pivot Tables | 1 | 3 |
| **TOTAL** | **26** | **213 fields** |

---

## Key Design Decisions

### Soft Deletes (9 models)
- Account, Transaction, Budget, Invoice
- Subscription, RecurringTransaction, CsvImport
- Integration, ReportExport, Notification
- User (for data retention)

**Why**: Financial data must be recoverable; auditing/compliance

### Decimal for Money
```php
'amount' => 'decimal:2'  // Always 2 decimal places
```

**Why**: Floating point errors in financial calculations

### User-Centric Design
- User is primary key in most models
- Authorization checks user_id
- Security: Users can only see their data

### Event-Driven Hooks
- TransactionCreated → Update account balance
- BudgetExceeded → Send notification
- SubscriptionExpiring → Send reminder

### Indexes for Performance
- Foreign keys indexed
- Frequent query columns indexed
- Composite indexes on common joins

---

For implementation examples, see [Development Guide](DEVELOPMENT.md)
For migrations, see `database/migrations/`
