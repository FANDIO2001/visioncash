# Contributing to VisionCash

Thank you for contributing to VisionCash! This guide helps you get started.

## Code of Conduct

Be respectful, inclusive, and professional. We're all here to build something great together.

---

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR-USERNAME/visioncash.git`
3. Create a feature branch: `git checkout -b feature/your-feature`
4. Follow the [Development Guide](docs/DEVELOPMENT.md)
5. Submit a pull request

---

## Development Setup

See [SETUP.md - Development Setup](docs/SETUP.md#development-setup) for complete instructions.

Quick start:

```bash
git clone https://github.com/YOUR-USERNAME/visioncash.git
cd visioncash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

---

## Coding Standards

### PHP Style (PSR-12)

```php
// Follow PHP Coding Standards
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

// PascalCase for classes
class UserController {

    // camelCase for methods
    public function getUserProfile(): UserResource {
        // 4-space indentation
        // Max 120 characters per line
    }

    // Always use type hints
    private function validateUser(User $user): bool {
        return $user->is_active;
    }
}
```

### Naming Conventions

- Classes: `PascalCase` (UserController, CreateAccountRequest)
- Methods: `camelCase` (getUserAccounts)
- Properties: `camelCase` ($userName)
- Database columns: `snake_case` (user_name)
- Constants: `UPPER_SNAKE_CASE` (MAX_ATTEMPTS)

### Comments

```php
// Use comments to explain WHY, not WHAT
// ✅ GOOD
// Check if user exceeded account limit before creating
if ($accountCount >= 10) { }

// ❌ AVOID
// If account count >= 10
if ($accountCount >= 10) { }
```

---

## Testing Requirements

**All new features must include tests:**

```bash
# Run tests
composer test

# Run specific test
php artisan test tests/Feature/CreateAccountTest.php

# Check coverage
php artisan test --coverage
```

### Test Structure

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class CreateAccountTest extends TestCase {

    /**
     * @test
     * User can create new account
     */
    public function user_can_create_account() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/accounts', [
                'account_name' => 'Savings',
                'account_type_id' => 1,
                'currency' => 'USD',
                'balance' => 5000,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'account_name' => 'Savings',
        ]);
    }
}
```

---

## Commit Messages

Follow conventional commit format:

```
[type] Description of change

feat: Add budget alert notifications
fix: Fix transaction date filtering
refactor: Extract validation to service
docs: Update API documentation
test: Add account creation tests
chore: Update dependencies
```

### Types

- `feat` - New feature
- `fix` - Bug fix
- `refactor` - Code refactoring
- `docs` - Documentation
- `test` - Tests
- `chore` - Dependencies, config

### Examples

```
✅ GOOD
feat: Add recurring transaction support
fix: Handle null balance in account query
docs: Add deployment guide
test: Add budget validation tests

❌ AVOID
Update stuff
Fix it
Changes
```

---

## Pull Request Process

1. **Update from main**

```bash
git fetch origin
git rebase origin/main
```

2. **Test your changes**

```bash
composer test
composer pint  # Check code style
php artisan optimize
```

3. **Push and create PR**

```bash
git push origin feature/your-feature
```

4. **PR Template**

```markdown
## Description

Brief description of changes

## Type of Change

- [ ] New feature
- [ ] Bug fix
- [ ] Breaking change
- [ ] Documentation

## Testing

Describe how you tested this change

## Checklist

- [ ] Tests pass: `composer test`
- [ ] Code is formatted: `composer pint`
- [ ] Documentation updated
- [ ] No console.log or debug code
- [ ] Commit messages follow convention
```

---

## Feature Development Checklist

When building a new feature:

- [ ] **Models**: Created/updated in `app/Models/`
- [ ] **Migrations**: Created in `database/migrations/`
- [ ] **Controller**: Created in `app/Http/Controllers/`
- [ ] **Requests**: Validation classes in `app/Http/Requests/`
- [ ] **Resources**: JSON output in `app/Http/Resources/`
- [ ] **Service**: Business logic in `app/Services/`
- [ ] **Routes**: Added to `routes/api.php`
- [ ] **Tests**: Unit + Feature tests in `tests/`
- [ ] **Documentation**: Updated in `docs/`
- [ ] **Types**: Proper type hints throughout
- [ ] **Comments**: Brief comments explaining why

### Example: Add Transaction Categories

```bash
# 1. Create model (if not exists)
php artisan make:model Category

# 2. Create migration
php artisan make:migration create_transaction_categories_table

# 3. Create controller
php artisan make:controller CategoryController --resource

# 4. Create request classes
php artisan make:request StoreCategoryRequest
php artisan make:request UpdateCategoryRequest

# 5. Create resource class
php artisan make:resource CategoryResource

# 6. Create service (if needed)
# app/Services/CategoryService.php

# 7. Add routes
# In routes/api.php: Route::apiResource('categories', CategoryController::class);

# 8. Write tests
# tests/Feature/CreateCategoryTest.php
# tests/Unit/CategoryTest.php

# 9. Run tests
php artisan test

# 10. Format code
composer pint

# 11. Commit
git add .
git commit -m "feat: Add transaction category management"

# 12. Push
git push origin feature/add-categories

# 13. Create PR on GitHub
```

---

## Code Review Process

### For Authors

- Keep PRs focused and manageable
- Explain complex changes
- Respond to feedback constructively
- Update PR based on review comments

### For Reviewers

- Check functionality and design
- Ensure tests are comprehensive
- Verify code follows standards
- Provide constructive feedback
- Approve when satisfied

---

## Common Issues

### Tests Failing

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Reset database
php artisan migrate:refresh

# Run tests again
php artisan test
```

### Code Style Issues

```bash
# Auto-fix style issues
composer pint

# Check specific file
./vendor/bin/pint app/Models/User.php
```

### Database Conflicts

```bash
# Create new migration
php artisan make:migration add_field_to_table

# Fresh start (dev only!)
php artisan migrate:fresh
```

---

## Documentation Updates

When changing functionality:

1. Update relevant doc file in `docs/`
2. Update `docs/API.md` for endpoint changes
3. Update `docs/MODELS.md` if schema changed
4. Update `README.md` if user-facing
5. Include doc changes in PR

---

## Performance Considerations

- Use eager loading: `Account::with('user')->get()`
- Avoid N+1 queries
- Add indexes for frequent queries
- Use database transactions for consistency
- Cache expensive operations
- Monitor query performance

---

## Security Guidelines

- Never commit `.env` files with secrets
- Validate all user input
- Use prepared statements (Eloquent does this)
- Hash sensitive data
- Implement authorization checks
- Follow OWASP guidelines

---

## Reporting Issues

Found a bug? Create a GitHub issue with:

1. **Description**: What's the problem?
2. **Steps to Reproduce**: How to trigger it?
3. **Expected Behavior**: What should happen?
4. **Actual Behavior**: What does happen?
5. **Environment**: PHP version, Laravel version, OS
6. **Screenshots**: If applicable

---

## Feature Requests

Have an idea? Create an issue with:

1. **Title**: Clear, concise
2. **Description**: Explain the feature
3. **Use Case**: Why is it needed?
4. **Acceptance Criteria**: How to know it's done?
5. **Related Issues**: Any similar requests?

---

## Getting Help

- **Questions**: Check [docs/](docs/) first
- **Stuck?**: Ask in GitHub Discussions
- **Bug?**: Create a GitHub issue
- **Security**: Email privately, don't create public issue

---

## Recognition

Contributors are recognized in:

- Git commit history
- Project README
- Release notes

Thank you for contributing! 🙌

---

Last updated: May 29, 2026
