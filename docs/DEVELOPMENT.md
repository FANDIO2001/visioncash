# Development Guide - VisionCash

Coding standards, conventions, and best practices for VisionCash development.

## Code Style & Standards

### PHP Code Style (PSR-12)

**Naming Conventions:**

```php
// Classes: PascalCase
class UserController { }
class CreateTransactionRequest { }

// Methods: camelCase
public function getUserAccounts() { }

// Functions: snake_case
function get_user_accounts() { }

// Constants: UPPER_SNAKE_CASE
const MAX_LOGIN_ATTEMPTS = 5;
define('API_VERSION', 'v1');

// Variables: camelCase
$userName = 'John Doe';
$isActive = true;

// Database columns: snake_case
Schema::create('user_accounts', function (Blueprint $table) {
    $table->id();
    $table->string('account_number');
    $table->decimal('account_balance', 10, 2);
});
```

### Type Hints (Mandatory)

```php
// ✅ ALWAYS use type hints
public function store(StoreAccountRequest $request): AccountResource {
    $account = $this->service->create($request->validated());
    return new AccountResource($account);
}

// ❌ AVOID untyped parameters
public function store($request) { }
public function getAccounts() { }

// Use strict types
declare(strict_types=1);
```

### Comment Style

```php
// Single line comments for brief explanations
// Use for "why", not "what"

/**
 * Create a new transaction.
 *
 * @param array $data Transaction data
 * @return Transaction The created transaction
 * @throws InsufficientFundsException
 */
public function create(array $data): Transaction {
    // Implementation
}

// ❌ Avoid obvious comments
$user = User::find($id); // Get user by ID
```

### Formatting

```php
// Lines: Max 120 characters
// Indentation: 4 spaces (NOT tabs)
// Blank lines: 1 between methods

class AccountController extends Controller {
    public function index(): Collection {
        return Account::byUser(auth()->id())->get();
    }

    public function store(StoreAccountRequest $request): AccountResource {
        $account = $this->service->create($request->validated());
        return new AccountResource($account);
    }
}
```

---

## File Organization

### Controller Structure

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Response;

class AccountController extends Controller {

    public function __construct(private AccountService $service) {}

    /**
     * List accounts for authenticated user
     */
    public function index(): Response {
        $accounts = Account::byUser(auth()->id())->get();
        return response()->json(AccountResource::collection($accounts));
    }

    /**
     * Create new account
     */
    public function store(StoreAccountRequest $request): Response {
        $account = $this->service->create($request->validated());
        return response()->json(new AccountResource($account), 201);
    }

    /**
     * Get account by ID
     */
    public function show(Account $account): Response {
        $this->authorize('view', $account);
        return response()->json(new AccountResource($account));
    }

    /**
     * Update account
     */
    public function update(UpdateAccountRequest $request, Account $account): Response {
        $this->authorize('update', $account);
        $account = $this->service->update($account, $request->validated());
        return response()->json(new AccountResource($account));
    }

    /**
     * Delete account
     */
    public function destroy(Account $account): Response {
        $this->authorize('delete', $account);
        $this->service->delete($account);
        return response()->noContent();
    }
}
```

### Model Structure

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Account extends Model {

    use SoftDeletes;

    // ===== PROPERTIES =====

    protected $fillable = [
        'user_id',
        'account_type_id',
        'account_name',
        'balance',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }

    // ===== SCOPES =====

    public function scopeActive(Builder $query): Builder {
        return $query->where('is_active', true);
    }

    public function scopeByUser(Builder $query, int $userId): Builder {
        return $query->where('user_id', $userId);
    }

    // ===== ACCESSORS & MUTATORS =====

    public function getFormattedBalanceAttribute(): string {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }
}
```

### Service Structure

```php
<?php

namespace App\Services;

use App\Events\AccountCreated;
use App\Exceptions\InvalidAccountException;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountService {

    /**
     * Create new account with validation
     */
    public function create(array $data): Account {
        // Validate business logic
        $this->validateAccountLimit($data['user_id']);

        return DB::transaction(function () use ($data) {
            $account = Account::create($data);

            // Dispatch events
            event(new AccountCreated($account));

            return $account;
        });
    }

    /**
     * Update existing account
     */
    public function update(Account $account, array $data): Account {
        return DB::transaction(function () use ($account, $data) {
            $account->update($data);
            return $account;
        });
    }

    /**
     * Delete account (soft delete)
     */
    public function delete(Account $account): bool {
        if ($account->transactions()->exists()) {
            throw new InvalidAccountException('Cannot delete account with transactions');
        }

        return $account->delete();
    }

    /**
     * Validate user account limit
     */
    private function validateAccountLimit(int $userId): void {
        $count = Account::byUser($userId)->count();

        if ($count >= 10) {
            throw new InvalidAccountException('Maximum 10 accounts allowed');
        }
    }
}
```

---

## Testing Standards

### Unit Tests

Test models, services, and business logic in isolation:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Services\AccountService;
use Tests\TestCase;

class AccountServiceTest extends TestCase {

    private AccountService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = app(AccountService::class);
    }

    public function test_create_account() {
        $data = [
            'user_id' => 1,
            'account_name' => 'Savings',
            'balance' => 1000,
            'currency' => 'USD',
        ];

        $account = $this->service->create($data);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('Savings', $account->account_name);
        $this->assertDatabaseHas('accounts', $data);
    }

    public function test_max_accounts_limit() {
        // Setup 10 existing accounts
        Account::factory(10)->create(['user_id' => 1]);

        $this->expectException(InvalidAccountException::class);

        $this->service->create([
            'user_id' => 1,
            'account_name' => 'Eleventh Account',
            'balance' => 100,
            'currency' => 'USD',
        ]);
    }
}
```

### Feature Tests

Test complete API flows:

```php
<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Tests\TestCase;

class CreateAccountTest extends TestCase {

    public function test_authenticated_user_can_create_account() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', [
            'account_name' => 'Checking',
            'account_type_id' => 1,
            'currency' => 'USD',
            'balance' => 5000,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'account_name',
            'balance',
            'created_at',
        ]);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'account_name' => 'Checking',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_account() {
        $response = $this->postJson('/api/v1/accounts', [
            'account_name' => 'Checking',
        ]);

        $response->assertStatus(401);
    }

    public function test_validation_errors_returned() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', [
            'account_name' => '', // Required
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_name');
    }
}
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific file
php artisan test tests/Feature/CreateAccountTest.php

# Run with coverage
php artisan test --coverage

# Watch mode (reruns on file changes)
php artisan test --watch

# Only failed tests
php artisan test --only-failures
```

---

## Debugging & Tools

### Laravel Tinker (REPL)

```bash
# Open interactive shell
php artisan tinker

# In Tinker:
>>> $user = User::first();
>>> $user->accounts;
>>> Account::active()->get();
>>> DB::table('users')->count();
```

### Laravel Debugbar

```php
// In .env
DEBUGBAR_ENABLED=true

// Use in code:
debugbar()->info('Debug message');
debugbar()->measure('operation', function () {
    // Code to measure
});
```

### Laravel Pail (Log Viewer)

```bash
# View logs in real-time
php artisan pail

# Filter by level
php artisan pail --level=error

# Filter by type
php artisan pail --filter="TransactionCreated"
```

### Database Inspector

```bash
# Check queries run
php artisan query:log

# View migrations
php artisan migrate:status

# Show model properties
php artisan tinker
>>> User::first()->getFillable();
>>> User::first()->getHidden();
>>> User::first()->getCasts();
```

---

## Common Tasks

### Create a New Resource

1. **Create Migration**

```bash
php artisan make:migration create_resources_table
```

2. **Create Model**

```bash
php artisan make:model Resource --migration --factory --seeder
```

3. **Create Controller**

```bash
php artisan make:controller ResourceController --resource
```

4. **Create Request Classes**

```bash
php artisan make:request StoreResourceRequest
php artisan make:request UpdateResourceRequest
```

5. **Create Resource Class**

```bash
php artisan make:resource ResourceResource
```

6. **Add Routes** (routes/api.php)

```php
Route::apiResource('resources', ResourceController::class);
```

### Debug a Problem

1. Check logs:

```bash
php artisan pail --level=error
```

2. Check database:

```bash
php artisan tinker
>>> Resource::where('id', 123)->first();
```

3. Check request/response:

```php
// In controller
logger()->debug('Request:', $request->all());
logger()->debug('User:', auth()->user());
```

4. Test query:

```bash
php artisan tinker
>>> Resource::where('status', 'active')->toSql();
>>> Resource::where('status', 'active')->toRawSql();
```

---

## Git Workflow

### Branch Naming

```
feature/add-budget-alerts      # New feature
bugfix/fix-transaction-sync    # Bug fix
refactor/optimize-queries      # Code improvement
docs/update-api-docs           # Documentation
```

### Commit Messages

```
# Format: [Type] Description

✓ GOOD:
[feature] Add budget alert notifications
[bugfix] Fix transaction date filtering
[refactor] Extract validation to service

✗ AVOID:
"Update stuff"
"Fix"
"Trying to make this work"
```

### Pull Requests

- Write clear description
- Link related issues
- Add tests
- Request review from team member

---

## Performance Tips

### Query Optimization

```php
// ❌ SLOW: N+1 problem
foreach (Account::all() as $account) {
    echo $account->user->name; // Extra query per account
}

// ✅ FAST: Eager loading
foreach (Account::with('user')->get() as $account) {
    echo $account->user->name; // Only 2 queries
}

// ✅ SELECTIVE: Specify columns
Account::select('id', 'user_id', 'balance')
    ->with('user:id,name')
    ->get();
```

### Caching

```php
// Cache expensive queries
$accounts = Cache::remember("user.{$userId}.accounts", 3600, function () {
    return Account::byUser(auth()->id())->get();
});

// Invalidate when changed
Cache::forget("user.{$userId}.accounts");
```

### Database

```php
// Use indexes (migrations already have them)
Schema::table('transactions', function (Blueprint $table) {
    $table->index(['user_id', 'account_id']);
});

// Batch operations
Account::upsert($accounts, ['id'], ['balance', 'updated_at']);
```

---

## Resources

- [Laravel Docs](https://laravel.com/docs)
- [PHP PSR Standards](https://www.php-fig.org/)
- [Clean Code](https://www.oreilly.com/library/view/clean-code-a/9780136083238/)
- [Refactoring](https://refactoring.guru/)

---

Last updated: May 29, 2026
