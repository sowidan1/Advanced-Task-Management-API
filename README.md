# Task Management API

A robust, feature-rich Laravel-based RESTful API for comprehensive task management with advanced workflows, notifications, and full documentation.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Documentation](#api-documentation)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)

## Features

### Core Functionality
- **Complete CRUD Operations** - Create, read, update, and delete tasks
- **Status Management** - Workflow-based status transitions (Pending → In Progress → Completed)
- **Priority System** - Three-tier priority levels (Low, Medium, High)
- **Soft Deletion** - Recoverable task deletion with data preservation
- **Advanced Filtering** - Filter by status, priority, date ranges, and creation date
- **Comprehensive Validation** - Input validation with meaningful error responses

### Advanced Features
- **Full-Text Search** - Powered by Laravel Scout for title and description search
- **Automated Notifications** - Email alerts 24 hours before task due dates
- **Background Processing** - Queue-based email delivery and task monitoring
- **Rate Limiting** - API throttling (60 requests/minute) for optimal performance
- **Swagger Documentation** - Auto-generated OpenAPI documentation
- **Token Authentication** - Secure API access using Laravel Sanctum

## Requirements

- **PHP** 8.1 or higher
- **Composer** 2.0+
- **MySQL** 8.0+

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/sowidan1/Advanced-Task-Management-API.git
cd Advanced-Task-Management-API
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Migration

```bash
php artisan migrate
```

### 5. Generate API Documentation

```bash
php artisan l5-swagger:generate
```

## Configuration

### Environment Variables

Configure your `.env` file with the following settings:

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Search Configuration
```env
SCOUT_DRIVER=database
```

#### Queue Configuration
```env
QUEUE_CONNECTION=database
```

#### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Background Tasks

Start the queue worker for processing notifications:

```bash
php artisan queue:work
```

## API Documentation

### Swagger UI
Access the interactive API documentation at:
```
http://your-domain/api/documentation
```

## Authentication

This API uses **Laravel Sanctum** for token-based authentication.

### Registration
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Using the Token
Include the token in all subsequent requests:
```http
Authorization: Bearer your-api-token-here
```

## API Endpoints

### Authentication Routes
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | User registration |
| POST | `/api/login` | User login |
| POST | `/api/logout` | User logout |

### Task Management Routes
All task routes require authentication and are rate-limited to 60 requests per minute.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks` | List all tasks with filtering options |
| POST | `/api/tasks` | Create a new task |
| GET | `/api/tasks/{id}` | Get specific task details |
| PUT | `/api/tasks/{id}` | Update a task |
| DELETE | `/api/tasks/{id}` | Delete a task (soft delete) |
| PATCH | `/api/tasks/{id}/status` | Update task status |
| GET | `/api/tasks/search` | Search tasks by title/description |

### Query Parameters

#### Filtering Tasks
```http
GET /api/tasks?status=pending&priority=high&due_date_from=2024-01-01&due_date_to=2024-12-31
```

#### Searching Tasks
```http
GET /api/tasks/search?q=project+meeting
```


## Testing

### Running Tests

Execute the complete test suite:
```bash
php artisan test
````

## Technology Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **Search**: Laravel Scout
- **Queue System**: Laravel Queue
- **Documentation**: L5-Swagger (OpenAPI 3.0)
- **Testing**: PHPUnit
- **Email**: Laravel Mail with SMTP

## Performance Considerations

- **Database Indexing**: Optimized indexes on frequently queried columns
- **Caching**: caching for improved response times
- **Queue Processing**: Background job processing for email notifications
- **Rate Limiting**: API throttling to prevent abuse

**Developed with ❤️ by [Osama Sowidan]** - Software Engineer.
