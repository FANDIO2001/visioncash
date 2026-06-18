# VisionCash Documentation Index

Complete documentation for VisionCash - Personal Finance Management API.

## 📚 Quick Links

### For Users & PMs

- 🎯 [README.md](../README.md) - Project overview and features
- 🚀 [Getting Started](SETUP.md#development-setup) - Quick start in 5 minutes

### For Developers

**Setup & Deployment:**

- 🔧 [Complete Setup Guide](SETUP.md) - Installation, configuration, deployment
- 🗄️ [Environment Variables](.env.example) - Configuration template

**API Development:**

- 📡 [API Reference](API.md) - Complete endpoint documentation with examples
- 🏗️ [Architecture Guide](ARCHITECTURE.md) - System design and patterns
- 💻 [Development Standards](DEVELOPMENT.md) - Code style, testing, best practices
- 📊 [Database Models](MODELS.md) - Schema, relationships, 26 models documented

**Quick Reference:**

- [API Authentication](API.md#authentication)
- [Error Handling](API.md#error-responses)
- [Rate Limiting](API.md#rate-limiting)
- [Testing Guide](DEVELOPMENT.md#testing-standards)

---

## 📋 Documentation Structure

```
docs/
├── INDEX.md                 ← You are here
├── SETUP.md                # Installation & deployment (400 lines)
├── API.md                  # API reference (600 lines)
├── ARCHITECTURE.md         # System design (500 lines)
├── DEVELOPMENT.md          # Coding standards (400 lines)
├── MODELS.md              # Database schema (500 lines)
└── examples/              # Code examples (coming soon)
```

---

## 🎯 By Role

### Backend Developer

1. Read [SETUP.md](SETUP.md) - Get development environment working
2. Read [ARCHITECTURE.md](ARCHITECTURE.md) - Understand system design
3. Read [DEVELOPMENT.md](DEVELOPMENT.md) - Learn coding standards
4. Read [MODELS.md](MODELS.md) - Understand database schema
5. Check [API.md](API.md) - Reference for endpoints

### Frontend Developer

1. Read [SETUP.md](SETUP.md#installation) - Get development server running
2. Read [API.md](API.md) - Learn API endpoints and responses
3. Check [ARCHITECTURE.md](ARCHITECTURE.md#api-flow) - Understand request/response flow
4. Look at code examples in [DEVELOPMENT.md](DEVELOPMENT.md#testing-standards)

### DevOps / System Admin

1. Read [SETUP.md](SETUP.md#production-deployment) - Production checklist
2. Check [SETUP.md](SETUP.md#deployment-steps) - Nginx/Apache setup
3. Review [.env.example](.env.example) - Environment configuration
4. Follow [Troubleshooting](SETUP.md#troubleshooting) - Common issues

### Project Manager / Product Owner

1. Read [README.md](../README.md) - Project overview
2. Check [API.md](API.md#base-information) - API capabilities
3. Review features in [README.md](../README.md#core-functionality)

---

## 🔑 Key Features

| Feature         | Status          | Documentation                                                        |
| --------------- | --------------- | -------------------------------------------------------------------- |
| User Management | ✅ Models Ready | [MODELS.md#1-user](MODELS.md#1-user)                                 |
| Accounts        | ✅ Models Ready | [MODELS.md#5-account](MODELS.md#5-account)                           |
| Transactions    | ✅ Models Ready | [MODELS.md#9-transaction](MODELS.md#9-transaction)                   |
| Budgets         | ✅ Models Ready | [MODELS.md#12-budget](MODELS.md#12-budget)                           |
| Subscriptions   | ✅ Models Ready | [MODELS.md#24-subscription](MODELS.md#24-subscription)               |
| Integrations    | ✅ Models Ready | [MODELS.md#15-integration](MODELS.md#15-integration)                 |
| Notifications   | ✅ Models Ready | [MODELS.md#18-notification](MODELS.md#18-notification)               |
| API Endpoints   | 🔨 In Progress  | [API.md](API.md)                                                     |
| Controllers     | 🔨 To Build     | [DEVELOPMENT.md](DEVELOPMENT.md)                                     |
| Tests           | 🔨 To Build     | [DEVELOPMENT.md#testing-standards](DEVELOPMENT.md#testing-standards) |

---

## 📖 Documentation by Topic

### Getting Started

- [5-Minute Quick Start](SETUP.md#step-1-clone-repository)
- [Full Installation Guide](SETUP.md#development-setup)
- [Prerequisites](SETUP.md#system-requirements)

### Database & Models

- [Database Overview](MODELS.md)
- [Complete Schema](MODELS.md#model-hierarchy)
- [26 Models Reference](MODELS.md#models-reference)
- [Relationships](MODELS.md#entity-relationship-diagram-simplified)
- [Design Decisions](MODELS.md#key-design-decisions)

### API

- [Authentication](API.md#authentication)
- [Endpoints Reference](API.md#resources)
- [Error Handling](API.md#error-responses)
- [Code Examples](API.md#examples)
- [Rate Limiting](API.md#rate-limiting)

### Architecture & Code

- [System Architecture](ARCHITECTURE.md#overview)
- [Design Patterns](ARCHITECTURE.md#architectural-patterns)
- [Code Structure](ARCHITECTURE.md#folder-structure)
- [Best Practices](ARCHITECTURE.md#best-practices)

### Development

- [Coding Standards](DEVELOPMENT.md#code-style--standards)
- [Testing Guide](DEVELOPMENT.md#testing-standards)
- [File Organization](DEVELOPMENT.md#file-organization)
- [Debugging Tools](DEVELOPMENT.md#debugging--tools)
- [Common Tasks](DEVELOPMENT.md#common-tasks)

### Deployment

- [Production Checklist](SETUP.md#pre-deployment-checklist)
- [Deployment Steps](SETUP.md#deployment-steps)
- [Environment Configuration](SETUP.md#configure-environment)
- [Troubleshooting](SETUP.md#troubleshooting)

---

## 🔍 Find Something Specific

**How do I...**

- **Set up development environment?**
  → [SETUP.md - Development Setup](SETUP.md#development-setup)

- **Deploy to production?**
  → [SETUP.md - Production Deployment](SETUP.md#production-deployment)

- **Create a new API endpoint?**
  → [DEVELOPMENT.md - Controller Structure](DEVELOPMENT.md#controller-structure)

- **Understand the database schema?**
  → [MODELS.md](MODELS.md)

- **Write tests?**
  → [DEVELOPMENT.md - Testing Standards](DEVELOPMENT.md#testing-standards)

- **Call an API endpoint from code?**
  → [API.md - Examples](API.md#examples)

- **Debug a problem?**
  → [SETUP.md - Troubleshooting](SETUP.md#troubleshooting)

- **Optimize performance?**
  → [DEVELOPMENT.md - Performance Tips](DEVELOPMENT.md#performance-tips)

- **Understand authentication?**
  → [API.md - Authentication](API.md#authentication) or [ARCHITECTURE.md - Authentication Flow](ARCHITECTURE.md#authentication-flow)

- **Configure environment variables?**
  → [.env.example](.env.example) or [SETUP.md - Configure Environment](SETUP.md#step-3-configure-environment)

---

## 📊 Project Statistics

| Metric                  | Value                  |
| ----------------------- | ---------------------- |
| **Models**              | 26 Eloquent models     |
| **Database Tables**     | 29 tables + pivots     |
| **Migrations**          | 39+ migrations         |
| **Fields Documented**   | 213+ database fields   |
| **API Endpoints**       | ~35 planned (to build) |
| **Documentation Lines** | 2700+ lines            |
| **Code Examples**       | 50+ examples           |

---

## 🛠️ Tech Stack

- **Backend**: Laravel 13.8, PHP 8.3+
- **Database**: MySQL 8+ / PostgreSQL 12+ (SQLite for dev)
- **Authentication**: Laravel Sanctum
- **Frontend**: Vite, TailwindCSS 4, Node.js
- **Testing**: PHPUnit 12.5, Mockery
- **Tools**: Composer, npm, Artisan CLI

---

## 📞 Support

- **Documentation**: You're reading it!
- **Issues**: Create GitHub issue with [SETUP.md - Troubleshooting](SETUP.md#troubleshooting)
- **Questions**: Check relevant section above
- **Contributing**: Follow [DEVELOPMENT.md](DEVELOPMENT.md#code-style--standards)

---

## 🎓 Learning Path

**New to VisionCash?**

1. Read [README.md](../README.md) (5 min)
2. Follow [SETUP.md](SETUP.md) (30 min)
3. Explore [MODELS.md](MODELS.md) (15 min)
4. Review [API.md](API.md) (20 min)

**Building a feature?**

1. Check [ARCHITECTURE.md](ARCHITECTURE.md) for design patterns
2. Read [DEVELOPMENT.md](DEVELOPMENT.md) for coding standards
3. Use [MODELS.md](MODELS.md) for database reference
4. Follow examples in relevant doc

**Deploying to production?**

1. Use [SETUP.md - Production Deployment](SETUP.md#production-deployment)
2. Verify [Pre-Deployment Checklist](SETUP.md#pre-deployment-checklist)
3. Follow step-by-step [Deployment Steps](SETUP.md#deployment-steps)

---

## 📝 Documentation Status

- ✅ README.md - Complete
- ✅ .env.example - Complete with all options
- ✅ SETUP.md - Complete (dev + production)
- ✅ API.md - Complete (all endpoints documented)
- ✅ ARCHITECTURE.md - Complete (patterns + design)
- ✅ DEVELOPMENT.md - Complete (standards + examples)
- ✅ MODELS.md - Complete (26 models documented)
- ✅ INDEX.md - This file

---

**Last Updated**: May 29, 2026  
**Version**: 0.1.0 (Early Development)

---

**Next Steps**: Controllers and API endpoints are ready to be built! Follow [DEVELOPMENT.md](DEVELOPMENT.md#controller-structure) for guidance.
