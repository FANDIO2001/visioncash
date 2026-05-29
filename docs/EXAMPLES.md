# Code Examples - VisionCash

Practical examples for common development tasks.

## Table of Contents
1. [API Authentication](#api-authentication)
2. [Creating Resources](#creating-resources)
3. [Querying Data](#querying-data)
4. [Business Logic](#business-logic)
5. [Testing](#testing)
6. [Frontend Integration](#frontend-integration)

---

## API Authentication

### Login & Get Token

```bash
# cURL
curl -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Response
{
  "data": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John"
  },
  "token": "1|ABC123DEF456xyz...",
  "expires_at": "2026-06-28T10:00:00Z"
}
```

### Use Token in Requests

```bash
# cURL with token
curl -X GET "http://localhost:8000/api/v1/accounts" \
  -H "Authorization: Bearer 1|ABC123DEF456xyz..." \
  -H "Accept: application/json"
```

### Python Example

```python
import requests
import json

BASE_URL = "http://localhost:8000/api/v1"

# Login
login_response = requests.post(f"{BASE_URL}/auth/login", json={
    "email": "user@example.com",
    "password": "password123"
})

token = login_response.json()["token"]

# Use token
headers = {
    "Authorization": f"Bearer {token}",
    "Content-Type": "application/json"
}

# Get accounts
accounts = requests.get(f"{BASE_URL}/accounts", headers=headers)
print(accounts.json())
```

### JavaScript Example

```javascript
const BASE_URL = "http://localhost:8000/api/v1";

async function login() {
  const response = await fetch(`${BASE_URL}/auth/login`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      email: "user@example.com",
      password: "password123"
    })
  });
  
  const data = await response.json();
  return data.token;
}

async function getAccounts(token) {
  const response = await fetch(`${BASE_URL}/accounts`, {
    headers: {
      "Authorization": `Bearer ${token}`,
      "Content-Type": "application/json"
    }
  });
  
  return response.json();
}

// Usage
const token = await login();
const accounts = await getAccounts(token);
console.log(accounts);
```

---

## Creating Resources

### Create Account

```php
// In Controller
use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\AccountService;

class AccountController extends Controller {
    
    public function __construct(private AccountService $service) {}
    
    public function store(StoreAccountRequest $request): AccountResource {
        $account = $this->service->create($request->validated());
        return new AccountResource($account);
    }
}

// In Service
namespace App\Services;

use App\Models\Account;

class AccountService {
    
    public function create(array $data): Account {
        return DB::transaction(function () use ($data) {
            $account = Account::create($data);
            event(new AccountCreated($account));
            return $account;
        });
    }
}

// In Request
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest {
    
    public function authorize(): bool {
        return true;
    }
    
    public function rules(): array {
        return [
            'account_type_id' => 'required|exists:account_types,id',
            'account_name' => 'required|string|max:255',
            'currency' => 'required|in:USD,EUR,GBP',
            'balance' => 'required|numeric|min:0',
        ];
    }
}

// In Resource
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource {
    
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->account_name,
            'balance' => $this->balance,
            'formatted_balance' => $this->formatted_balance,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
```

### API Call

```bash
curl -X POST "http://localhost:8000/api/v1/accounts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "account_type_id": 1,
    "account_name": "Savings Account",
    "currency": "USD",
    "balance": 5000.00
  }'

# Response (201 Created)
{
  "id": 1,
  "name": "Savings Account",
  "balance": 5000.00,
  "formatted_balance": "5000.00 USD",
  "is_active": true,
  "created_at": "2026-05-29T10:00:00Z"
}
```

---

## Querying Data

### Simple Query with Scopes

```php
// ❌ AVOID: Raw queries
$accounts = Account::where('user_id', auth()->id())
    ->where('is_active', true)
    ->get();

// ✅ GOOD: Using scopes
$accounts = Account::byUser(auth()->id())->active()->get();

// In Model
class Account extends Model {
    public function scopeByUser(Builder $query, int $userId) {
        return $query->where('user_id', $userId);
    }
    
    public function scopeActive(Builder $query) {
        return $query->where('is_active', true);
    }
}
```

### Complex Query with Relationships

```php
// Get transactions with eager loading
$transactions = Transaction::byUser(auth()->id())
    ->with(['account', 'category'])  // Eager load relationships
    ->income()
    ->betweenDates($startDate, $endDate)
    ->orderByDesc('transaction_date')
    ->paginate(15);

// In Model
class Transaction extends Model {
    public function scopeIncome(Builder $query) {
        return $query->where('transaction_type', 'income');
    }
    
    public function scopeBetweenDates(Builder $query, $start, $end) {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }
}
```

### Search & Filter

```php
// In Controller
public function index(Request $request) {
    $query = Transaction::byUser(auth()->id());
    
    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }
    
    if ($request->has('type')) {
        $query->where('transaction_type', $request->type);
    }
    
    if ($request->has('search')) {
        $query->where('description', 'like', "%{$request->search}%");
    }
    
    return TransactionResource::collection(
        $query->paginate(15)
    );
}
```

### Aggregations

```php
// Total spending by category
$spending = Transaction::expense()
    ->byUser(auth()->id())
    ->where('transaction_date', '>=', now()->subMonth())
    ->groupBy('category_id')
    ->selectRaw('category_id, SUM(amount) as total')
    ->get();

// Account totals
$totalBalance = Account::byUser(auth()->id())->sum('balance');

// Count
$accountCount = Account::byUser(auth()->id())->count();

// Average
$avgTransaction = Transaction::byUser(auth()->id())
    ->avg('amount');
```

---

## Business Logic

### Transaction with Balance Update

```php
// In Service
namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService {
    
    public function create(array $data): Transaction {
        return DB::transaction(function () use ($data) {
            // Validate
            $account = Account::findOrFail($data['account_id']);
            
            if ($data['type'] === 'expense' && 
                $account->balance < $data['amount']) {
                throw new InsufficientFundsException();
            }
            
            // Create transaction
            $transaction = Transaction::create($data);
            
            // Update account balance
            if ($data['type'] === 'expense') {
                $account->decrement('balance', $data['amount']);
            } else {
                $account->increment('balance', $data['amount']);
            }
            
            // Dispatch event
            event(new TransactionCreated($transaction));
            
            return $transaction;
        });
    }
}

// In Controller
public function store(StoreTransactionRequest $request) {
    try {
        $transaction = $this->service->create($request->validated());
        return new TransactionResource($transaction);
    } catch (InsufficientFundsException $e) {
        return response()->json([
            'error' => 'Insufficient funds',
            'current_balance' => $account->balance
        ], 422);
    }
}
```

### Budget Alert Logic

```php
// In Service
class BudgetService {
    
    public function checkAndAlert(Budget $budget): void {
        $percentageSpent = ($budget->spent / $budget->amount) * 100;
        
        if ($percentageSpent >= 100 && !$budget->alert_sent_100) {
            $this->sendAlert($budget, '100% Budget Exceeded!');
            $budget->update(['alert_sent_100' => true]);
        } elseif ($percentageSpent >= 80 && !$budget->alert_sent_80) {
            $this->sendAlert($budget, '80% Budget Reached');
            $budget->update(['alert_sent_80' => true]);
        }
    }
    
    private function sendAlert(Budget $budget, string $message): void {
        Notification::create([
            'user_id' => $budget->user_id,
            'title' => 'Budget Alert',
            'message' => $message,
            'type' => 'warning'
        ]);
    }
}

// In Event Listener
class TransactionCreated {
    public function handle(TransactionCreated $event) {
        $budget = Budget::where('category_id', $event->transaction->category_id)
            ->where('user_id', $event->transaction->user_id)
            ->first();
        
        if ($budget) {
            $budget->increment('spent', $event->transaction->amount);
            app(BudgetService::class)->checkAndAlert($budget);
        }
    }
}
```

### CSV Import Processing

```php
// In Job
use App\Models\CsvImport;
use App\Models\Transaction;

class ProcessCsvImport {
    
    public function handle(CsvImport $import) {
        $import->update(['status' => 'processing']);
        
        try {
            $file = Storage::get($import->file_path);
            $rows = str_getcsv($file, "\n");
            
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header
                
                $data = str_getcsv($row);
                
                Transaction::create([
                    'user_id' => $import->user_id,
                    'account_id' => $import->account_id,
                    'amount' => $data[0],
                    'description' => $data[1],
                    'transaction_date' => $data[2],
                    'created_by_source' => 'csv'
                ]);
                
                $import->increment('imported_count');
            }
            
            $import->update(['status' => 'completed']);
        } catch (Exception $e) {
            $import->update([
                'status' => 'failed',
                'error_details' => $e->getMessage()
            ]);
        }
    }
}
```

---

## Testing

### Unit Test for Service

```php
namespace Tests\Unit\Services;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Services\TransactionService;
use Tests\TestCase;

class TransactionServiceTest extends TestCase {
    
    private TransactionService $service;
    
    protected function setUp(): void {
        parent::setUp();
        $this->service = app(TransactionService::class);
    }
    
    public function test_create_expense_transaction() {
        $account = Account::factory()->create(['balance' => 1000]);
        
        $transaction = $this->service->create([
            'account_id' => $account->id,
            'amount' => 50,
            'type' => 'expense',
            'category_id' => 1
        ]);
        
        $this->assertEquals(50, $transaction->amount);
        $this->assertEquals(950, $account->fresh()->balance);
    }
    
    public function test_insufficient_funds_exception() {
        $account = Account::factory()->create(['balance' => 10]);
        
        $this->expectException(InsufficientFundsException::class);
        
        $this->service->create([
            'account_id' => $account->id,
            'amount' => 50,
            'type' => 'expense',
            'category_id' => 1
        ]);
    }
}
```

### Feature Test for API

```php
namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Tests\TestCase;

class CreateTransactionTest extends TestCase {
    
    public function test_user_can_create_transaction() {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        
        $response = $this->actingAs($user)->postJson('/api/v1/transactions', [
            'account_id' => $account->id,
            'amount' => 50,
            'type' => 'expense',
            'category_id' => 1,
            'description' => 'Groceries'
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'amount', 'created_at']);
        
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'amount' => 50
        ]);
    }
    
    public function test_validation_fails_without_required_fields() {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/v1/transactions', []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['account_id', 'amount']);
    }
}
```

---

## Frontend Integration

### React Hook for API Calls

```javascript
// useApi.js
import { useState, useEffect } from 'react';

const useApi = (url, token) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetch(`http://localhost:8000/api/v1${url}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      setData(data);
      setLoading(false);
    })
    .catch(err => {
      setError(err);
      setLoading(false);
    });
  }, [url, token]);

  return { data, loading, error };
};

// Usage in component
function Accounts({ token }) {
  const { data: accounts, loading, error } = useApi('/accounts', token);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <ul>
      {accounts?.map(account => (
        <li key={account.id}>
          {account.name}: {account.formatted_balance}
        </li>
      ))}
    </ul>
  );
}
```

### Vue 3 Composition API

```javascript
// useAccounts.js
import { ref, onMounted } from 'vue';

export function useAccounts(token) {
  const accounts = ref([]);
  const loading = ref(true);

  onMounted(async () => {
    const response = await fetch('http://localhost:8000/api/v1/accounts', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    accounts.value = data.data;
    loading.value = false;
  });

  return { accounts, loading };
}

// In component
<template>
  <div v-if="loading">Loading...</div>
  <ul v-else>
    <li v-for="account in accounts" :key="account.id">
      {{ account.name }}: {{ account.formatted_balance }}
    </li>
  </ul>
</template>

<script setup>
import { useAccounts } from '@/composables/useAccounts';

const { accounts, loading } = useAccounts(props.token);
</script>
```

### Error Handling

```javascript
// apiClient.js
const api = async (method, endpoint, data = null, token) => {
  try {
    const response = await fetch(`http://localhost:8000/api/v1${endpoint}`, {
      method,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: data ? JSON.stringify(data) : null
    });

    if (response.status === 401) {
      // Token expired, refresh or redirect to login
      window.location.href = '/login';
      return;
    }

    if (response.status === 422) {
      const errors = await response.json();
      throw new ValidationError(errors.errors);
    }

    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status}`);
    }

    return response.json();
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
};

// Usage
try {
  const account = await api('POST', '/accounts', {
    account_name: 'Savings',
    balance: 5000
  }, token);
} catch (error) {
  if (error instanceof ValidationError) {
    // Show validation errors
    displayErrors(error.errors);
  } else {
    // Show generic error
    showNotification('Error creating account');
  }
}
```

---

## More Examples

For more code examples, check:
- [docs/DEVELOPMENT.md](../docs/DEVELOPMENT.md#code-style--standards)
- [docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md#api-flow)
- [docs/API.md](../docs/API.md#examples)
- [tests/](../tests/) directory for test examples

---

Last updated: May 29, 2026
