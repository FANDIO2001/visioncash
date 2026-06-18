# API Documentation - VisionCash

Complete REST API reference for VisionCash with request/response examples.

## Base Information

- **Base URL**: `http://localhost:8000/api/v1` (development)
- **Production URL**: `https://api.visioncash.com/api/v1`
- **Response Format**: JSON
- **Authentication**: Bearer Token (Laravel Sanctum)

---

## Authentication

### Obtaining a Token

**Endpoint:**

```
POST /auth/login
```

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response (200 OK):**

```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "user@example.com",
        "default_currency": "USD",
        "created_at": "2026-05-29T10:00:00Z"
    },
    "token": "1|ABC123DEF456xyz...xyz",
    "expires_at": "2026-06-28T10:00:00Z"
}
```

### Using the Token

Include token in all API requests:

```
Authorization: Bearer 1|ABC123DEF456xyz...xyz
```

### Logout

```
POST /auth/logout
```

Response:

```json
{
    "message": "Logged out successfully"
}
```

---

## Error Responses

All errors follow this format:

```json
{
    "error": true,
    "message": "Validation failed",
    "status": 422,
    "errors": {
        "email": ["The email field is required"],
        "amount": ["The amount must be numeric"]
    }
}
```

### Error Codes

| Code | Meaning                              |
| ---- | ------------------------------------ |
| 400  | Bad Request                          |
| 401  | Unauthorized (invalid/missing token) |
| 403  | Forbidden (no permission)            |
| 404  | Not Found                            |
| 422  | Validation Error                     |
| 500  | Server Error                         |

---

## Resources

### Users

#### List Users

```
GET /users
```

**Parameters:**

- `page` (int) - Page number (default: 1)
- `per_page` (int) - Results per page (default: 15, max: 100)
- `sort_by` (string) - Sort field (id, created_at, email)
- `sort_order` (string) - asc or desc

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "default_currency": "USD",
            "language": "en",
            "is_active": true,
            "avatar_url": null,
            "created_at": "2026-05-29T10:00:00Z"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/v1/users?page=1",
        "last": "http://localhost:8000/api/v1/users?page=5",
        "next": "http://localhost:8000/api/v1/users?page=2"
    },
    "meta": {
        "current_page": 1,
        "total": 100,
        "per_page": 15
    }
}
```

#### Get Current User

```
GET /user
```

**Response:**

```json
{
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "default_currency": "USD",
    "theme": "light",
    "timezone": "UTC",
    "notifications_enabled": true,
    "created_at": "2026-05-29T10:00:00Z"
}
```

#### Get User by ID

```
GET /users/{id}
```

#### Create User

```
POST /users
```

**Request Body:**

```json
{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "password": "secure_password_123",
    "password_confirmation": "secure_password_123",
    "default_currency": "USD",
    "language": "en",
    "timezone": "UTC"
}
```

#### Update User

```
PUT /users/{id}
```

**Request Body (all fields optional):**

```json
{
    "first_name": "Jane",
    "last_name": "Smith",
    "default_currency": "EUR",
    "theme": "dark",
    "timezone": "Europe/Paris"
}
```

#### Delete User

```
DELETE /users/{id}
```

---

### Accounts

#### List User Accounts

```
GET /accounts
```

**Parameters:**

- `is_active` (boolean) - Filter by active status
- `sort_by` (string) - Sort field (created_at, balance)

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "account_type_id": 1,
            "account_name": "Checking Account",
            "account_number": "****1234",
            "is_active": true,
            "currency": "USD",
            "balance": 5250.5,
            "formatted_balance": "5250.50 USD",
            "initial_balance": 5000.0,
            "color": "#3B82F6",
            "iban": "US64ZBZS960P68957662",
            "is_overdrawn": false,
            "created_at": "2026-05-29T10:00:00Z"
        }
    ]
}
```

#### Get Account Details

```
GET /accounts/{id}
```

#### Create Account

```
POST /accounts
```

**Request Body:**

```json
{
    "account_type_id": 1,
    "account_name": "Savings Account",
    "account_number": "98765432",
    "currency": "USD",
    "initial_balance": 10000.0,
    "color": "#10B981",
    "iban": "US64ZBZS960P68957662"
}
```

#### Update Account

```
PUT /accounts/{id}
```

#### Delete Account

```
DELETE /accounts/{id}
```

#### Get Account Transactions

```
GET /accounts/{id}/transactions
```

**Parameters:**

- `type` (string) - income, expense
- `start_date` (date) - YYYY-MM-DD
- `end_date` (date) - YYYY-MM-DD
- `limit` (int) - Number of results

#### Get Account Balance History

```
GET /accounts/{id}/balance-history
```

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "account_id": 1,
            "balance": 5250.5,
            "recorded_at": "2026-05-29T10:00:00Z",
            "created_at": "2026-05-29T10:00:00Z"
        }
    ]
}
```

---

### Transactions

#### List Transactions

```
GET /transactions
```

**Parameters:**

- `account_id` (int) - Filter by account
- `category_id` (int) - Filter by category
- `type` (string) - income, expense
- `start_date` (date) - YYYY-MM-DD
- `end_date` (date) - YYYY-MM-DD
- `page` (int)
- `per_page` (int)

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "account_id": 1,
            "category_id": 5,
            "amount": 50.0,
            "transaction_type": "expense",
            "description": "Grocery shopping",
            "currency": "USD",
            "formatted_amount": "-50.00",
            "is_manual": true,
            "transaction_date": "2026-05-29",
            "created_at": "2026-05-29T10:00:00Z"
        }
    ],
    "meta": {
        "total": 250,
        "per_page": 15,
        "current_page": 1
    }
}
```

#### Get Transaction Details

```
GET /transactions/{id}
```

#### Create Transaction

```
POST /transactions
```

**Request Body:**

```json
{
    "account_id": 1,
    "category_id": 5,
    "amount": 50.0,
    "transaction_type": "expense",
    "description": "Groceries",
    "currency": "USD",
    "is_manual": true,
    "transaction_date": "2026-05-29"
}
```

**Response (201 Created):**

```json
{
    "id": 1,
    "account_id": 1,
    "category_id": 5,
    "amount": 50.0,
    "transaction_type": "expense",
    "description": "Groceries",
    "created_at": "2026-05-29T10:00:00Z"
}
```

#### Update Transaction

```
PUT /transactions/{id}
```

#### Delete Transaction

```
DELETE /transactions/{id}
```

#### Upload Attachment

```
POST /transactions/{id}/attachments
```

**Request (multipart/form-data):**

```
file: <binary file>
description: Receipt for groceries
```

---

### Budgets

#### List Budgets

```
GET /budgets
```

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "category_id": 5,
            "amount": 500.0,
            "spent": 250.5,
            "percentage_spent": 50.1,
            "remaining": 249.5,
            "period_type": "monthly",
            "start_date": "2026-05-01",
            "end_date": "2026-05-31",
            "alert_threshold_percentage": 80,
            "is_exceeded": false,
            "is_active": true,
            "created_at": "2026-05-29T10:00:00Z"
        }
    ]
}
```

#### Get Budget Details

```
GET /budgets/{id}
```

#### Create Budget

```
POST /budgets
```

**Request Body:**

```json
{
    "category_id": 5,
    "amount": 500.0,
    "period_type": "monthly",
    "start_date": "2026-06-01",
    "end_date": "2026-06-30",
    "alert_threshold_percentage": 80,
    "is_active": true
}
```

#### Update Budget

```
PUT /budgets/{id}
```

#### Delete Budget

```
DELETE /budgets/{id}
```

#### Get Budget History

```
GET /budgets/{id}/history
```

---

### Categories

#### List Categories

```
GET /categories
```

**Parameters:**

- `type` (string) - income, expense
- `is_active` (boolean)

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "name": "Groceries",
            "icon": "shopping-cart",
            "color": "#FF6B6B",
            "type": "expense",
            "is_default": false,
            "is_active": true,
            "created_at": "2026-05-29T10:00:00Z"
        }
    ]
}
```

#### Get Category Details

```
GET /categories/{id}
```

#### Create Category

```
POST /categories
```

**Request Body:**

```json
{
    "name": "Entertainment",
    "type": "expense",
    "icon": "film",
    "color": "#9B59B6",
    "is_active": true
}
```

#### Update Category

```
PUT /categories/{id}
```

#### Delete Category

```
DELETE /categories/{id}
```

---

### Subscriptions

#### List Subscriptions

```
GET /subscriptions
```

**Parameters:**

- `status` (string) - active, trial, past_due, expired, cancelled
- `plan_id` (int)

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "plan_id": 1,
            "status": "active",
            "current_period_start": "2026-05-29",
            "current_period_end": "2026-06-29",
            "trial_ends_at": null,
            "ended_at": null,
            "created_at": "2026-05-29T10:00:00Z"
        }
    ]
}
```

#### Get Subscription Details

```
GET /subscriptions/{id}
```

#### Create Subscription

```
POST /subscriptions
```

**Request Body:**

```json
{
    "plan_id": 1,
    "payment_method_id": 1,
    "coupon_code": "SAVE20"
}
```

#### Get Subscription Invoices

```
GET /subscriptions/{id}/invoices
```

---

### Integrations

#### List Integrations

```
GET /integrations
```

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "provider_id": 1,
            "provider_name": "Plaid",
            "account_id": null,
            "is_active": true,
            "last_synced_at": "2026-05-29T10:00:00Z",
            "next_sync_at": "2026-05-30T10:00:00Z",
            "created_at": "2026-05-29T10:00:00Z"
        }
    ]
}
```

#### Create Integration

```
POST /integrations
```

**Request Body:**

```json
{
    "provider_id": 1,
    "access_token": "public-token-from-plaid",
    "account_id": 1
}
```

#### Sync Integration

```
POST /integrations/{id}/sync
```

**Response:**

```json
{
    "message": "Sync started",
    "sync_log_id": 42
}
```

#### Get Sync Logs

```
GET /integrations/{id}/sync-logs
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Default**: 60 requests per minute
- **Response Headers**:
    ```
    X-RateLimit-Limit: 60
    X-RateLimit-Remaining: 58
    X-RateLimit-Reset: 1622329200
    ```

When rate limited, you'll receive a **429 Too Many Requests** response.

---

## Pagination

Endpoints that return lists support pagination:

**Parameters:**

- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 15, max: 100)

**Response Includes:**

```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "path": "http://...",
    "per_page": 15,
    "to": 15,
    "total": 250
  }
}
```

---

## Filtering & Sorting

Supported query parameters vary by endpoint. Common patterns:

```
GET /transactions?type=expense&start_date=2026-05-01&sort_by=amount&sort_order=desc
```

---

## Status Codes

| Code | Meaning          |
| ---- | ---------------- |
| 200  | OK               |
| 201  | Created          |
| 204  | No Content       |
| 400  | Bad Request      |
| 401  | Unauthorized     |
| 403  | Forbidden        |
| 404  | Not Found        |
| 422  | Validation Error |
| 429  | Rate Limited     |
| 500  | Server Error     |

---

## Examples

### cURL

```bash
# Get all accounts
curl -X GET "http://localhost:8000/api/v1/accounts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Create transaction
curl -X POST "http://localhost:8000/api/v1/transactions" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "account_id": 1,
    "category_id": 5,
    "amount": 50.00,
    "transaction_type": "expense",
    "description": "Groceries"
  }'
```

### Python (Requests)

```python
import requests

BASE_URL = "http://localhost:8000/api/v1"
TOKEN = "your_token_here"

headers = {
    "Authorization": f"Bearer {TOKEN}",
    "Content-Type": "application/json"
}

# Get accounts
response = requests.get(f"{BASE_URL}/accounts", headers=headers)
accounts = response.json()

# Create transaction
payload = {
    "account_id": 1,
    "category_id": 5,
    "amount": 50.00,
    "transaction_type": "expense",
    "description": "Groceries"
}
response = requests.post(
    f"{BASE_URL}/transactions",
    json=payload,
    headers=headers
)
```

### JavaScript (Fetch)

```javascript
const BASE_URL = "http://localhost:8000/api/v1";
const TOKEN = "your_token_here";

const headers = {
    Authorization: `Bearer ${TOKEN}`,
    "Content-Type": "application/json",
};

// Get accounts
fetch(`${BASE_URL}/accounts`, { headers })
    .then((res) => res.json())
    .then((data) => console.log(data));

// Create transaction
const payload = {
    account_id: 1,
    category_id: 5,
    amount: 50.0,
    transaction_type: "expense",
    description: "Groceries",
};

fetch(`${BASE_URL}/transactions`, {
    method: "POST",
    headers,
    body: JSON.stringify(payload),
})
    .then((res) => res.json())
    .then((data) => console.log(data));
```

---

For more info, see [Setup Guide](SETUP.md) and [Architecture Guide](ARCHITECTURE.md).
