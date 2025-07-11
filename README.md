# Shipping Tracking Web Application

A Laravel-based web application for managing shipping orders and tracking deliveries using Biteship API integration.

## Features

-   **Authentication & Authorization**

    -   JWT/API Token-based authentication using Laravel Sanctum
    -   Role-based access control (Admin & User roles)
    -   User registration, login, logout
    -   Profile management
    -   Password management

-   **User Management**

    -   Admin can create, read, update, delete users
    -   Users can manage their own profiles
    -   Role-based permissions

-   **Order Management**

    -   Create and manage shipping orders
    -   Order status tracking with multiple states
    -   User-specific order access and admin oversight
    -   Order statistics and reporting
    -   Order cancellation functionality

-   **Biteship Integration**
    -   Order creation and management via Biteship API
    -   Comprehensive shipment tracking system
    -   Public tracking interface for customers

## Technology Stack

-   **Backend**: Laravel 12
-   **Database**: PostgreSQL
-   **Authentication**: Laravel Sanctum
-   **API**: RESTful API
-   **Testing**: PHPUnit with SQLite in-memory database
-   **Containerization**: Docker & Docker Compose

## Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Docker & Docker Compose (recommended)
-   PostgreSQL (if not using Docker)
-   Git

### Setup

#### Option 1: Docker Setup (Recommended)

1. **Clone the repository**

    ```bash
    git clone git@github.com:bimadewantoro/shipping-tracking-web.git
    cd shipping-tracking-web
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Environment configuration**

    ```bash
    cp .env.example .env
    ```

    Update the following environment variables in `.env`:

    ```env
    APP_NAME="Shipping Tracking"
    APP_URL=http://localhost:8000

    DB_CONNECTION=pgsql
    DB_HOST=postgres
    DB_PORT=5432
    DB_DATABASE=shipping_tracking_web
    DB_USERNAME=root
    DB_PASSWORD=

    BITESHIP_API_KEY=your_biteship_api_key
    BITESHIP_API_URL=https://api.biteship.com/v1
    ```

4. **Start PostgreSQL with Docker**

    ```bash
    # Start PostgreSQL container
    make up

    # Check container status
    make status

    # View PostgreSQL logs
    make logs-db
    ```

5. **Generate application key**

    ```bash
    php artisan key:generate
    ```

6. **Setup database (migrate & seed)**

    ```bash
    # Run migrations and seeders
    make db-setup

    # Or run them separately
    make db-migrate
    make db-seed
    ```

7. **Start the development server**

    ```bash
    php artisan serve
    ```

#### Option 2: Manual Setup

1. **Clone the repository**

    ```bash
    git clone git@github.com:bimadewantoro/shipping-tracking-web.git
    cd shipping-tracking-web
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Environment configuration**

    ```bash
    cp .env.example .env
    ```

    Update the following environment variables in `.env`:

    ```env
    APP_NAME="Shipping Tracking"
    APP_URL=http://localhost:8000

    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=shipping_tracking_web
    DB_USERNAME=your_username
    DB_PASSWORD=your_password

    BITESHIP_API_KEY=your_biteship_api_key
    BITESHIP_API_URL=https://api.biteship.com/v1
    ```

4. **Create PostgreSQL database**

    ```sql
    CREATE DATABASE shipping_tracking_web;
    ```

5. **Generate application key**

    ```bash
    php artisan key:generate
    ```

6. **Run database migrations**

    ```bash
    php artisan migrate
    ```

7. **Seed the database**

    ```bash
    php artisan db:seed
    ```

    This will create:

    - Admin user: `admin@example.com` / `admin123`
    - Regular user: `user@example.com` / `user123`

8. **Start the development server**
    ```bash
    php artisan serve
    ```

## Docker & Database Management

### Available Make Commands

The project includes a comprehensive Makefile for easy Docker and database management. All commands are designed to work seamlessly with the Docker Compose setup.

#### Docker Operations

```bash
# Start PostgreSQL service
make up              # Start PostgreSQL container in detached mode

# Stop services
make down            # Stop PostgreSQL container gracefully

# Service management
make restart         # Restart PostgreSQL container
make status          # Show current container status

# Logging and monitoring
make logs            # View logs from all services
make logs-db         # View PostgreSQL logs specifically

# Clean up (⚠️ Destructive operations)
make clean           # Remove containers, networks, and volumes (deletes all data)
```

#### Database Operations

```bash
# Database access
make db-shell        # Open interactive PostgreSQL shell (psql)

# Database setup and management
make db-migrate      # Run Laravel database migrations
make db-seed         # Run Laravel database seeders
make db-setup        # Complete setup: run migrations + seeders

# Database reset (⚠️ Destructive operation)
make db-reset        # Drop and recreate database schema (deletes all data)
```

#### Common Workflows

```bash
# Initial project setup
make up && make db-setup

# Daily development
make up              # Start database
# ... develop your application ...
make logs-db         # Check database logs if needed

# Reset everything for testing
make down && make clean && make up && make db-setup

# Quick database refresh
make db-reset && make db-setup

# Complete cleanup
make down && make clean
```

### Docker Configuration

The Docker setup provides a robust development environment with the following components:

#### Services

-   **PostgreSQL 15 Alpine**: Primary database server
    -   **Container Name**: `shipping_tracking_postgres`
    -   **Port**: `5432` (mapped to host)
    -   **Database**: `shipping_tracking_web`
    -   **User**: `root`
    -   **Password**: None (trust authentication for development)
    -   **Auto-restart**: `unless-stopped`

#### Volumes

-   **postgres_data**: Persistent volume for database storage
    -   Ensures data survives container restarts
    -   Located at `/var/lib/postgresql/data` inside container

#### Networks

-   **shipping_tracking_network**: Isolated bridge network
    -   Provides network isolation for services
    -   Allows services to communicate using service names

#### Initialization

-   **Init Scripts**: Custom initialization scripts support
    -   Located in `./docker/postgres/init/`
    -   Executed when database is first created
    -   Useful for additional database setup or extensions

#### Environment Configuration

The PostgreSQL container is configured with:

```yaml
POSTGRES_DB: shipping_tracking_web
POSTGRES_USER: root
POSTGRES_PASSWORD: ""
POSTGRES_HOST_AUTH_METHOD: trust
```

**Note**: The `trust` authentication method is used for development convenience. In production, always use proper password authentication.

## API Documentation

### Base URL

```
http://localhost:8000/api
```

### Response Format

All API responses follow a consistent format:

```json
{
    "status": "success|error",
    "message": "Human readable message",
    "data": {
        // Response data (for success responses)
    }
}
```

### Postman Collection

For easy API testing and exploration, you can download and import the Postman collection:

**[Postman Collection](postman_collection.json)**

### Authentication

The API uses Laravel Sanctum for token-based authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your-token-here}
```

#### Register

-   **POST** `/auth/register`
-   **Description**: Register a new user account
-   **Body**:
    ```json
    {
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "081234567890",
        "password": "password123",
        "password_confirmation": "password123"
    }
    ```
-   **Response**:
    ```json
    {
        "status": "success",
        "message": "User registered successfully",
        "data": {
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "081234567890",
                "role": "user"
            },
            "token": "1|abc123...",
            "token_type": "Bearer"
        }
    }
    ```

#### Login

-   **POST** `/auth/login`
-   **Description**: Login with email and password
-   **Body**:
    ```json
    {
        "email": "john@example.com",
        "password": "password123",
        "remember": false
    }
    ```
-   **Response**:
    ```json
    {
        "status": "success",
        "message": "Login successful",
        "data": {
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "081234567890",
                "role": "user"
            },
            "token": "1|abc123...",
            "token_type": "Bearer"
        }
    }
    ```

#### Logout

-   **POST** `/auth/logout`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Logout and invalidate current token
-   **Response**:
    ```json
    {
        "status": "success",
        "message": "Logged out successfully"
    }
    ```

#### Get Current User

-   **GET** `/auth/me`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Get current authenticated user information
-   **Response**:
    ```json
    {
        "status": "success",
        "data": {
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "081234567890",
                "role": "user"
            }
        }
    }
    ```

### User Management

#### Get All Users (Admin Only)

-   **GET** `/users`
-   **Headers**: `Authorization: Bearer {token}`
-   **Query Parameters**:
    -   `per_page`: Number of items per page (default: 15, max: 100)
    -   `search`: Search by name, email, or phone
    -   `role`: Filter by role (admin/user)

#### Create User (Admin Only)

-   **POST** `/users`
-   **Headers**: `Authorization: Bearer {token}`
-   **Body**:
    ```json
    {
        "name": "New User",
        "email": "newuser@example.com",
        "phone": "081234567890",
        "password": "password123",
        "role": "user"
    }
    ```

#### Get User Details (Admin Only)

-   **GET** `/users/{id}`
-   **Headers**: `Authorization: Bearer {token}`

#### Update User (Admin Only)

-   **PUT** `/users/{id}`
-   **Headers**: `Authorization: Bearer {token}`
-   **Body**:
    ```json
    {
        "name": "Updated Name",
        "email": "updated@example.com",
        "phone": "081234567890",
        "role": "user"
    }
    ```

#### Delete User (Admin Only)

-   **DELETE** `/users/{id}`
-   **Headers**: `Authorization: Bearer {token}`

#### Get Own Profile

-   **GET** `/users/profile`
-   **Headers**: `Authorization: Bearer {token}`

#### Update Own Profile

-   **PUT** `/users/profile`
-   **Headers**: `Authorization: Bearer {token}`
-   **Body**:
    ```json
    {
        "name": "Updated Name",
        "email": "updated@example.com",
        "phone": "081234567890"
    }
    ```

#### Update Password

-   **PATCH** `/users/profile/password`
-   **Headers**: `Authorization: Bearer {token}`
-   **Body**:
    ```json
    {
        "current_password": "oldpassword",
        "password": "newpassword123",
        "password_confirmation": "newpassword123"
    }
    ```

### Order Management

#### Get All Orders

-   **GET** `/orders`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Get paginated list of orders (users see only their orders, admins see all)
-   **Query Parameters**:
    -   `per_page`: Number of items per page (default: 15, max: 100)
    -   `search`: Search by order number, sender, or receiver name
    -   `status`: Filter by order status (pending, confirmed, processing, shipped, delivered, cancelled)
-   **Response**:
    ```json
    {
        "status": "success",
        "data": {
            "orders": {
                "data": [
                    {
                        "id": 1,
                        "order_number": "ORD-20250112-001",
                        "status": "pending",
                        "sender_name": "John Doe",
                        "receiver_name": "Jane Smith",
                        "total_cost": 20000,
                        "created_at": "2025-01-12T10:30:00.000000Z"
                    }
                ],
                "current_page": 1,
                "per_page": 15,
                "total": 1
            }
        }
    }
    ```

#### Create Order

-   **POST** `/orders`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Create a new shipping order
-   **Body**:

    ```json
    {
        "sender_name": "John Doe",
        "sender_phone": "081234567890",
        "sender_address": "Jl. Sudirman No. 1",
        "sender_postal_code": "12345",
        "sender_area_id": "CGKKT02700",
        "sender_latitude": -6.2088,
        "sender_longitude": 106.8456,
        "receiver_name": "Jane Smith",
        "receiver_phone": "081234567891",
        "receiver_address": "Jl. Thamrin No. 2",
        "receiver_postal_code": "54321",
        "receiver_area_id": "CGKKT02800",
        "receiver_latitude": -6.1751,
        "receiver_longitude": 106.865,
        "package_type": "package",
        "package_weight": 1000,
        "package_length": 20,
        "package_width": 15,
        "package_height": 10,
        "package_description": "Electronics",
        "package_value": 100000,
        "courier_code": "jne",
        "courier_service": "reg",
        "insurance_cost": 5000,
        "notes": "Handle with care",
        "auto_create_biteship_order": false
    }
    ```

    **Required fields**: `sender_name`, `sender_phone`, `sender_address`, `sender_postal_code`, `receiver_name`, `receiver_phone`, `receiver_address`, `receiver_postal_code`, `package_weight`, `courier_code`, `courier_service`

    **Field Validations**:

    -   `package_weight`: Integer between 1-50000 grams (1g - 50kg)
    -   `package_type`: Either "package" or "document"
    -   `package_length`, `package_width`, `package_height`: Integer between 1-200 cm
    -   Coordinates: latitude (-90 to 90), longitude (-180 to 180)

#### Get Order Details

-   **GET** `/orders/{id}`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Get detailed information about a specific order

#### Confirm Order with Biteship

-   **POST** `/orders/{id}/confirm`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Create order in Biteship system and get tracking number

#### Cancel Order

-   **POST** `/orders/{id}/cancel`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Cancel an existing order
-   **Body**:
    ```json
    {
        "reason": "Changed mind about the delivery"
    }
    ```
    **Note**: `reason` field is optional (max 500 characters)
    ```

#### Track Order

-   **GET** `/orders/{id}/track`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Get tracking information for a specific order

#### Get Order Statistics

-   **GET** `/orders/statistics`
-   **Headers**: `Authorization: Bearer {token}`
-   **Description**: Get order statistics (users see their stats, admins see global stats)

### Public Tracking

#### Public Package Tracking

-   **GET** `/public/track`
-   **Description**: Track packages publicly using waybill ID and courier code (no authentication required)
-   **Query Parameters**:
    -   `waybill_id`: The waybill/tracking number (required)
    -   `courier_code`: The courier code (required)
-   **Example**: `/public/track?waybill_id=ABC123&courier_code=jne`

### Health Check

-   **GET** `/health`
-   **Description**: Health check endpoint to verify API status
-   **Response**:
    ```json
    {
        "status": "ok",
        "timestamp": "2025-01-11T10:30:00.000000Z",
        "service": "Shipping Tracking API"
    }
    ```

## Testing

The application uses SQLite with in-memory database for fast, isolated testing. All feature tests interact with a real database rather than using mocks to ensure accurate testing of database interactions.

### Running Tests

```bash
# Run all tests
php artisan test

# Run only feature tests
php artisan test --testsuite=Feature

# Run only unit tests
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/Auth/AuthTest.php

# Run with coverage (requires xdebug)
php artisan test --coverage
```

### Test Configuration

Tests are configured to use SQLite in-memory database (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) as defined in `phpunit.xml`. This ensures:

-   **Fast execution**: In-memory database is much faster than PostgreSQL
-   **Isolation**: Each test gets a fresh database state
-   **No setup required**: No need to configure a separate test database
-   **Real database interactions**: Tests use actual SQL queries, not mocks

## Code Quality

The project follows Laravel best practices:

-   **Single Responsibility Principle**: Each class has one responsibility
-   **Fat Models, Skinny Controllers**: Business logic in services and models
-   **Form Request Validation**: Separate request classes for validation
-   **Service Layer Pattern**: Business logic separated from controllers
-   **Proper Error Handling**: Consistent error responses
-   **Logging**: Important actions are logged
-   **Testing**: Comprehensive test coverage

## Security Features

-   **Password Hashing**: Bcrypt hashing for passwords
-   **API Token Authentication**: Secure token-based authentication
-   **Role-based Authorization**: Middleware for role checking
-   **Input Validation**: Comprehensive request validation
-   **SQL Injection Protection**: Eloquent ORM prevents SQL injection
-   **Rate Limiting**: API rate limiting (to be implemented)
