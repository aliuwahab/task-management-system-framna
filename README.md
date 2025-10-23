# Task Management System

A RESTful API for managing tasks built with **Symfony 7**, for assessment.


### Layers

```
src/
├── Domain/              # Business logic & entities (framework-independent)
│   ├── Entity/         # Task entity with business rules
│   ├── ValueObject/    # TaskId, TaskStatus (immutable)
│   ├── Repository/     # Repository interfaces
│   └── Exception/      # Domain exceptions
├── Application/         # Use cases (CQRS)
│   ├── Command/        # Write operations (Create, Update, Delete, ChangeStatus)
│   ├── Query/          # Read operations (GetById, GetAll)
│   └── DTO/            # Data Transfer Objects
├── Infrastructure/      # Technical implementations
│   └── Repository/     # Doctrine ORM implementation
├── Controller/          # API endpoints
│   └── Api/V1/         # Versioned controllers
└── Http/Request/        # Request DTOs with validation
    └── Api/V1/
```

## 🚀 Features

### Business Rules ✅
- ✅ A task cannot be deleted if its status is `done`
- ✅ A task can only be marked as "done" if it was previously `in_progress`
- ✅ A task title must be unique (enforced at application level)
- ✅ Task title is required and max 255 characters
- ✅ Task has: id (UUID), title, description (optional), status, createdAt, updatedAt

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/tasks` | Create a new task |
| `GET` | `/api/v1/tasks` | Get all tasks |
| `GET` | `/api/v1/tasks/{id}` | Get a single task |
| `PATCH` | `/api/v1/tasks/{id}` | Update task details |
| `PATCH` | `/api/v1/tasks/{id}/status` | Change task status |
| `DELETE` | `/api/v1/tasks/{id}` | Delete a task (if allowed) |
| `GET` | `/api/v1/docs` | Interactive API documentation (Swagger UI) |

## 📦 Installation

### Requirements
- PHP 8.3+
- Composer
- Symfony CLI (optional)

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd task-management-system-framna

# Install dependencies
composer install

# Configure database (SQLite by default)
# Edit .env if needed

# Create database schema
php bin/console doctrine:schema:create

# Run tests
php bin/phpunit

# Start development server
symfony serve -d
# Or: php -S localhost:8000 -t public
```

## 🧪 Testing

To run tests

```bash
# Run all tests
php bin/phpunit

# Run specific test suites
php bin/phpunit tests/Unit/              # Unit tests (Domain + Application)
php bin/phpunit tests/Integration/       # Integration tests (Repository)
php bin/phpunit tests/Functional/        # Functional tests (API endpoints)
```


## 📖 API Documentation

Interactive API documentation is available at:

**http://localhost:8000/api/v1/docs**

The documentation is auto-generated from code using **OpenAPI/Swagger** annotations.

## 🔧 Technology Stack

- **Symfony 7.3** - PHP framework
- **Doctrine ORM** - Database abstraction
- **Symfony Validator** - Request validation
- **Symfony Serializer** - JSON serialization
- **NelmioApiDocBundle** - OpenAPI documentation
- **PHPUnit** - Testing framework
- **SQLite** - Database (dev/test)

## 📝 Example Requests

### Create a Task
```bash
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Learn Symfony",
    "description": "Study Clean Architecture and CQRS"
  }'
```

### Get All Tasks
```bash
curl http://localhost:8000/api/v1/tasks
```

### Update Task
```bash
curl -X PATCH http://localhost:8000/api/v1/tasks/{id} \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Learn Symfony - Updated",
    "description": "Deep dive into CQRS"
  }'
```

### Change Status
```bash
curl -X PATCH http://localhost:8000/api/v1/tasks/{id}/status \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'
```

### Delete Task
```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/{id}
```

This project is for assessment purposes.

## 👤 Author
Gbeila Aliu Wahab
