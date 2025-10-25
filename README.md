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

### Bonus Features ⭐
- ✅ **Event Sourcing** - All task changes are recorded as domain events in `stored_events` table
- ✅ **Query Filtering** - Filter tasks by status: `GET /api/v1/tasks?status=todo`
- ✅ **In-Memory Repository** - Fast, database-free testing without mocks

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/tasks` | Create a new task |
| `GET` | `/api/v1/tasks` | Get all tasks (supports `?status=todo` filter) |
| `GET` | `/api/v1/tasks/{id}` | Get a single task |
| `PATCH` | `/api/v1/tasks/{id}` | Update task details |
| `PATCH` | `/api/v1/tasks/{id}/status` | Change task status |
| `DELETE` | `/api/v1/tasks/{id}` | Delete a task (if allowed) |
| `GET` | `/api/v1/docs` | Interactive API documentation (Swagger UI) |

## 📦 Installation

### Requirements
- PHP 8.3+
- Composer

### Quick Start (Automated)

```bash
git clone <repository-url>
cd task-management-system-framna
./setup.sh
```

**That's it!** The setup script will:
- ✅ Validate PHP version
- ✅ Copy environment configuration
- ✅ Install dependencies
- ✅ Create database schema
- ✅ Run tests to verify setup
- ✅ Optionally start the server

### Manual Setup (Alternative)

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Install dependencies
composer install

# 3. Create database schema
php bin/console doctrine:schema:create

# 4. Start server
php -S localhost:8000 -t public
```

The app is now running at **http://localhost:8000/api/v1/tasks**

### What's Pre-configured

- ✅ **SQLite database** - No database server needed
- ✅ **All environment variables** - Set with sensible defaults
- ✅ **CORS enabled** - Ready for frontend development
- ✅ **API documentation** - Available at `/api/v1/docs`

## 🧪 Testing

The project includes comprehensive tests with **87 tests and 281 assertions**.

```bash
# Run all tests
vendor/bin/phpunit

# Run with detailed output
vendor/bin/phpunit --testdox

# Run specific test suites
vendor/bin/phpunit tests/Unit/              # Unit tests (Domain + Application)
vendor/bin/phpunit tests/Integration/       # Integration tests (Repository)
vendor/bin/phpunit tests/Functional/        # Functional tests (API endpoints)
```

### In-Memory Repository for Testing

The project includes `InMemoryTaskRepository` for fast, database-free unit testing.

**Benefits:**
- 10-100x faster than database tests
- No mocking required - test real repository behavior
- Cleaner, more maintainable test code

**Usage Example:**

```php
// Instead of complex mocks
$repository = new InMemoryTaskRepository();
$command = new CreateTaskCommand($repository, $eventPublisher);

$taskId = $command->handle(new CreateTaskData(title: 'Test Task'));

// Direct verification
$this->assertEquals(1, $repository->count());
$task = $repository->findById(TaskId::fromString($taskId));
$this->assertEquals('Test Task', $task->getTitle());
```

See `tests/Unit/Application/Command/CreateTaskCommandWithInMemoryRepoTest.php` for complete examples.

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
# Get all tasks
curl http://localhost:8000/api/v1/tasks

# Filter by status
curl http://localhost:8000/api/v1/tasks?status=todo
curl http://localhost:8000/api/v1/tasks?status=in_progress
curl http://localhost:8000/api/v1/tasks?status=done
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
