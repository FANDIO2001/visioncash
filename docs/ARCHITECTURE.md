# Architecture Guide - VisionCash

Technical architecture and design patterns used in VisionCash.

## Table of Contents
1. [Overview](#overview)
2. [Folder Structure](#folder-structure)
3. [Architectural Patterns](#architectural-patterns)
4. [Database Design](#database-design)
5. [Authentication Flow](#authentication-flow)
6. [API Flow](#api-flow)
7. [Best Practices](#best-practices)

---

## Overview

VisionCash is built on a **layered architecture** using Laravel best practices:

```
┌─────────────────────────────────────────────┐
│           CLIENT (Web/Mobile)               │
├─────────────────────────────────────────────┤
│          API LAYER (Controllers)            │
├─────────────────────────────────────────────┤
│    BUSINESS LOGIC LAYER (Services)          │
├─────────────────────────────────────────────┤
│        DATA LAYER (Models/Eloquent)         │
├─────────────────────────────────────────────┤
│        DATABASE (MySQL/PostgreSQL)          │
└─────────────────────────────────────────────┘
```

### Key Principles
- **Separation of Concerns** - Each layer has single responsibility
- **DRY (Don't Repeat Yourself)** - Reusable code across app
- **SOLID Principles** - Maintainable, testable code
- **Type Safety** - Strong typing for predictability

---

## Folder Structure

```
visioncash/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API endpoints
│   │   │   ├── AuthController.php
│   │   │   ├── AccountController.php
│   │   │   ├── TransactionController.php
│   │   │   └── ...
│   │   │
│   │   ├── Requests/             # Request validation
│   │   │   ├── StoreAccountRequest.php
│   │   │   ├── UpdateTransactionRequest.php
│   │   │   └── ...
│   │   │
│   │   ├── Resources/            # JSON transformation
│   │   │   ├── AccountResource.php
│   │   │   ├── TransactionResource.php
│   │   │   └── ...
│   │   │
│   │   └── Middleware/           # Request middleware
│   │       ├── EnsureApiTokenValid.php
│   │       └── LogApiRequests.php
│   │
│   ├── Models/                   # Eloquent models (26 models)
│   │   ├── User.php
│   │   ├── Account.php
│   │   ├── Transaction.php
│   │   ├── Budget.php
│   │   ├── Subscription.php
│   │   ├── Invoice.php
│   │   └── ... (26 total)
│   │
│   ├── Services/                 # Business logic
│   │   ├── TransactionService.php
│   │   ├── BudgetService.php
│   │   ├── SubscriptionService.php
│   │   ├── IntegrationService.php
│   │   └── ...
│   │
│   ├── Exceptions/               # Custom exceptions
│   │   ├── InsufficientFundsException.php
│   │   ├── InvalidAccountException.php
│   │   └── ...
│   │
│   ├── Events/                   # Domain events
│   │   ├── TransactionCreated.php
│   │   ├── BudgetExceeded.php
│   │   └── ...
│   │
│   ├── Listeners/                # Event handlers
│   │   ├── SendBudgetAlert.php
│   │   ├── UpdateAccountBalance.php
│   │   └── ...
│   │
│   ├── Jobs/                     # Queue jobs
│   │   ├── ProcessCsvImport.php
│   │   ├── SyncIntegrations.php
│   │   ├── SendNotification.php
│   │   └── ...
│   │
│   ├── Policies/                 # Authorization
│   │   ├── AccountPolicy.php
│   │   ├── TransactionPolicy.php
│   │   └── ...
│   │
│   └── Providers/                # Service providers
│       ├── AppServiceProvider.php
│       └── RouteServiceProvider.php
│
├── config/                       # Configuration files
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   └── services.php
│
├── database/
│   ├── migrations/               # Schema migrations (39+)
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_05_28_192246_create_account_types_table.php
│   │   ├── 2026_05_28_192452_create_accounts_table.php
│   │   └── ...
│   │
│   ├── factories/                # Model factories (testing)
│   │   ├── UserFactory.php
│   │   └── ...
│   │
│   └── seeders/                  # Database seeders
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── AccountSeeder.php
│       └── ...
│
├── routes/
│   ├── api.php                   # API routes (v1, v2, etc.)
│   ├── web.php                   # Web routes
│   └── console.php               # Artisan commands
│
├── tests/
│   ├── Unit/                     # Unit tests (models, services)
│   │   ├── Models/
│   │   │   ├── UserTest.php
│   │   │   ├── AccountTest.php
│   │   │   └── ...
│   │   └── Services/
│   │       ├── TransactionServiceTest.php
│   │       └── ...
│   │
│   ├── Feature/                  # Feature tests (API endpoints)
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   ├── RegisterTest.php
│   │   │   └── LogoutTest.php
│   │   ├── Accounts/
│   │   │   ├── ListAccountsTest.php
│   │   │   ├── CreateAccountTest.php
│   │   │   └── ...
│   │   ├── Transactions/
│   │   │   ├── ListTransactionsTest.php
│   │   │   └── ...
│   │   └── ...
│   │
│   └── TestCase.php              # Base test class
│
├── storage/
│   ├── app/                      # File storage
│   ├── logs/                     # Application logs
│   └── framework/                # Framework cache
│
├── public/
│   ├── index.php                 # Entry point
│   └── ...
│
├── resources/
│   ├── css/                      # Frontend styles
│   ├── js/                       # Frontend scripts
│   └── views/                    # Blade templates
│
├── docs/                         # Documentation
│   ├── API.md                    # API reference
│   ├── ARCHITECTURE.md           # This file
│   ├── SETUP.md                  # Setup guide
│   ├── DEVELOPMENT.md            # Development standards
│   └── MODELS.md                 # Model documentation
│
├── .env.example                  # Environment template
├── composer.json                 # PHP dependencies
├── package.json                  # Node.js dependencies
├── vite.config.js                # Frontend bundler
├── phpunit.xml                   # Test configuration
└── README.md                     # Project overview
```

---

## Architectural Patterns

### 1. MVC Pattern
- **Models**: Eloquent models representing database tables
- **Views**: JSON responses (API)
- **Controllers**: HTTP request handlers

### 2. Service Layer Pattern
Business logic is encapsulated in services, not controllers:

```php
// ❌ WRONG - Logic in Controller
class TransactionController extends Controller {
    public function store(Request $request) {
        $account = Account::findOrFail($request->account_id);
        if ($account->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient funds'], 422);
        }
        $transaction = Transaction::create($request->validated());
        $account->decrement('balance', $request->amount);
        return new TransactionResource($transaction);
    }
}

// ✅ CORRECT - Logic in Service
class TransactionController extends Controller {
    public function __construct(private TransactionService $service) {}
    
    public function store(StoreTransactionRequest $request) {
        $transaction = $this->service->create($request->validated());
        return new TransactionResource($transaction);
    }
}

class TransactionService {
    public function create(array $data): Transaction {
        $account = Account::findOrFail($data['account_id']);
        
        if ($account->balance < $data['amount']) {
            throw new InsufficientFundsException();
        }
        
        $transaction = Transaction::create($data);
        $account->decrement('balance', $data['amount']);
        
        event(new TransactionCreated($transaction));
        
        return $transaction;
    }
}
```

### 3. Repository Pattern (Optional Enhancement)
For complex queries, use repositories:

```php
interface TransactionRepository {
    public function findByAccount(Account $account): Collection;
    public function findBetweenDates(Account $account, Carbon $start, Carbon $end): Collection;
}

class EloquentTransactionRepository implements TransactionRepository {
    public function findByAccount(Account $account): Collection {
        return $account->transactions()
            ->orderBy('transaction_date', 'desc')
            ->get();
    }
}
```

### 4. Factory Pattern
For complex object creation:

```php
class TransactionFactory {
    public static function create(array $data): Transaction {
        // Validation, transformation
        // Event dispatching
        // Audit logging
        return Transaction::create($data);
    }
}
```

### 5. Observer Pattern
Automatic model events:

```php
// app/Observers/TransactionObserver.php
class TransactionObserver {
    public function creating(Transaction $transaction) {
        $transaction->user_id ??= auth()->id();
    }
    
    public function created(Transaction $transaction) {
        event(new TransactionCreated($transaction));
    }
    
    public function updating(Transaction $transaction) {
        // Log changes for audit trail
    }
}

// Register in AppServiceProvider
public function boot(): void {
    Transaction::observe(TransactionObserver::class);
}
```

---

## Database Design

### Entity Relationship Diagram (Simplified)

```
┌─────────┐       ┌──────────┐       ┌──────────────┐
│  Users  │◄──────│ Accounts │───────► AccountTypes │
└─────────┘       └──────────┘       └──────────────┘
    ▲                   ▲
    │                   │
    │              ┌────────────┐
    └──────────────│Transactions│
    │              └────────────┘
    │                   ▲
    │                   │
    ├──────────────┬─────────────┐
    │              │             │
┌─────────┐   ┌────────┐   ┌──────────┐
│Budgets  │   │Invoices│   │Categories│
└─────────┘   └────────┘   └──────────┘
    │
┌──────────────┐
│Subscriptions │
└──────────────┘
```

### Key Principles

**1. Soft Deletes**
Critical tables use soft deletes for data recovery:
- Accounts, Transactions, Budgets, Invoices, Subscriptions
- Uses `deleted_at` timestamp column

**2. Proper Foreign Keys**
- `nullable()` for optional relationships
- `onDelete('set null')` for optional parent
- `onDelete('cascade')` for mandatory deletions
- `onDelete('restrict')` to prevent deletion of referenced records

**3. Indexes for Performance**
- Primary key indexes (auto)
- Foreign key indexes (for joins)
- Composite indexes for frequent queries:
  - `transactions(user_id, account_id, transaction_date)`
  - `invoices(subscription_id, status)`

**4. Type Casting**
All models use strict type casting:
```php
protected $casts = [
    'is_active' => 'boolean',
    'amount' => 'decimal:2',
    'transaction_date' => 'datetime',
    'deleted_at' => 'datetime',
];
```

---

## Authentication Flow

### Token-Based Authentication (Sanctum)

```
1. USER SENDS CREDENTIALS
   POST /api/v1/auth/login
   {
     "email": "user@example.com",
     "password": "password"
   }

2. SERVER VALIDATES & CREATES TOKEN
   └─ AuthController@login
   └─ Hash::check() password
   └─ Token::create() via Sanctum

3. SERVER RETURNS TOKEN
   {
     "token": "1|ABC123...",
     "expires_at": "2026-06-28..."
   }

4. CLIENT STORES TOKEN (localStorage, cookies)

5. SUBSEQUENT REQUESTS INCLUDE TOKEN
   Authorization: Bearer 1|ABC123...

6. MIDDLEWARE VALIDATES TOKEN
   └─ auth:sanctum middleware
   └─ Sanctum guards token validity
   └─ Auth user available in $request->user()

7. LOGOUT REVOKES TOKEN
   POST /api/v1/auth/logout
   └─ Token is revoked, can't use again
```

**Implementation:**
```php
// AuthController
class AuthController extends Controller {
    public function login(LoginRequest $request) {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials'
            ]);
        }
        
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        
        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
            'expires_at' => now()->addDays(30)
        ]);
    }
    
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
```

---

## API Flow

### Typical Request Processing

```
REQUEST
  │
  ├─ Router matches route
  │
  ├─ Middleware runs (auth:sanctum)
  │   └─ Validates Bearer token
  │   └─ Sets authenticated user
  │
  ├─ Controller action called
  │   └─ Dependency injection
  │
  ├─ Request validation (FormRequest)
  │   └─ StoreAccountRequest
  │   └─ Validates input
  │
  ├─ Service layer processes
  │   └─ TransactionService@create
  │   └─ Business logic
  │   └─ Events dispatched
  │
  ├─ Model operations
  │   └─ Eloquent queries
  │   └─ Database transactions
  │   └─ Observers triggered
  │
  ├─ Response transformation
  │   └─ Resource class (AccountResource)
  │   └─ JSON serialization
  │
  └─ Return JSON response
     {
       "id": 1,
       "name": "Savings",
       "balance": 1000.00,
       ...
     }
```

### Error Handling

```php
// In routes/api.php or error handler:
try {
    // Controller logic
} catch (InsufficientFundsException $e) {
    return response()->json([
        'error' => true,
        'message' => 'Insufficient funds',
        'code' => 'INSUFFICIENT_FUNDS'
    ], 422);
} catch (ModelNotFoundException $e) {
    return response()->json([
        'error' => true,
        'message' => 'Resource not found',
        'code' => 'NOT_FOUND'
    ], 404);
} catch (Throwable $e) {
    report($e);
    return response()->json([
        'error' => true,
        'message' => 'Server error',
        'code' => 'SERVER_ERROR'
    ], 500);
}
```

---

## Best Practices

### 1. Controllers Should Be Thin
```php
// ❌ FAT CONTROLLER
public function store(Request $request) {
    $validated = $request->validate([...]);
    if (Account::where('user_id', auth()->id())->count() >= 10) {
        return response()->json(['error' => 'Too many accounts'], 422);
    }
    $account = Account::create([...$validated]);
    // ... 50 more lines
}

// ✅ THIN CONTROLLER
public function store(StoreAccountRequest $request) {
    $account = $this->accountService->create($request->validated());
    return new AccountResource($account);
}
```

### 2. Use Query Scopes
```php
// ❌ REPETITIVE
$accounts = Account::where('user_id', auth()->id())
    ->where('is_active', true)
    ->get();

// ✅ CLEAN WITH SCOPES
$accounts = Account::byUser(auth()->id())->active()->get();

// In Model:
public function scopeByUser(Builder $query, $userId) {
    return $query->where('user_id', $userId);
}

public function scopeActive(Builder $query) {
    return $query->where('is_active', true);
}
```

### 3. Use Form Requests for Validation
```php
// app/Http/Requests/StoreAccountRequest.php
class StoreAccountRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Or check permissions
    }
    
    public function rules(): array {
        return [
            'account_type_id' => 'required|exists:account_types,id',
            'account_name' => 'required|string|max:255',
            'currency' => 'required|in:USD,EUR,GBP',
            'balance' => 'required|numeric|min:0',
        ];
    }
    
    public function messages(): array {
        return [
            'account_type_id.required' => 'Please select an account type',
            'currency.in' => 'Currency not supported',
        ];
    }
}
```

### 4. Use Resource Classes for JSON
```php
// app/Http/Resources/AccountResource.php
class AccountResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->account_name,
            'balance' => $this->balance,
            'formatted_balance' => $this->formatted_balance,
            'is_active' => $this->is_active,
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
        ];
    }
}
```

### 5. Eager Load Relationships
```php
// ❌ N+1 PROBLEM
foreach (Account::all() as $account) {
    echo $account->user->name; // Query per account!
}

// ✅ EAGER LOAD
foreach (Account::with('user')->get() as $account) {
    echo $account->user->name; // Only 2 queries total
}
```

### 6. Use Database Transactions
```php
DB::transaction(function () {
    $transaction = Transaction::create([...]);
    $account->decrement('balance', $transaction->amount);
    event(new TransactionCreated($transaction));
});
// All succeed or all rollback
```

### 7. Test Everything
```php
// tests/Feature/CreateTransactionTest.php
class CreateTransactionTest extends TestCase {
    public function test_user_can_create_transaction() {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        
        $response = $this->actingAs($user)->postJson('/api/v1/transactions', [
            'account_id' => $account->id,
            'amount' => 50,
            'type' => 'expense',
        ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'amount' => 50,
        ]);
    }
}
```

---

## Deployment Architecture

### Production Stack

```
Internet
  │
  ├─ CloudFlare (CDN)
  │
  ├─ Load Balancer (AWS ELB)
  │
  ├─ Web Servers (Nginx)
  │   ├─ Server 1 (Laravel App)
  │   ├─ Server 2 (Laravel App)
  │   └─ Server N (Laravel App)
  │
  ├─ Cache Layer (Redis)
  │
  ├─ Queue Workers
  │   ├─ Job Worker 1
  │   ├─ Job Worker 2
  │   └─ Job Worker N
  │
  ├─ Database (MySQL Primary + Replica)
  │
  └─ Storage (AWS S3)
```

---

## Technology Stack Summary

| Component | Technology | Purpose |
|-----------|-----------|---------|
| **Backend** | Laravel 13 | Web framework |
| **Language** | PHP 8.3+ | Server-side logic |
| **Database** | MySQL 8+ | Data storage |
| **Cache** | Redis | Performance |
| **Queue** | Redis | Background jobs |
| **Auth** | Sanctum | API tokens |
| **Frontend** | Vite + TailwindCSS | UI bundling |
| **Testing** | PHPUnit | Quality assurance |
| **Package Mgr** | Composer | PHP packages |
| **Node Mgr** | npm | Frontend packages |

---

For implementation details, see:
- [Setup Guide](SETUP.md)
- [API Reference](API.md)
- [Development Standards](DEVELOPMENT.md)
