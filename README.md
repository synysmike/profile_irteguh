# Profile Site - Laravel 11 with FrankenPHP

A modern profile management application built with Laravel 11, FrankenPHP, MySQL, Tailwind CSS, and jQuery.

## 🚀 Quick Start with Dev Containers

This project includes VS Code Dev Container configuration for seamless development:

1. Open the project in VS Code
2. Click "Reopen in Container" when prompted
3. Wait for the container to build
4. Run `composer install && php artisan key:generate && php artisan migrate && npm install && npm run build`

See [.devcontainer/README.md](.devcontainer/README.md) for more details.

## Features

- Docker setup with FrankenPHP and MySQL
- RESTful API for profile management
- Modern UI with Tailwind CSS
- jQuery for API interactions
- Full CRUD operations for profiles

## Requirements

- Docker and Docker Compose
- Node.js and npm (for building assets)

## Installation

### Quick Start (Using Makefile)

```bash
make install
npm install && npm run build
```

This will automatically:
- Build and start Docker containers
- Install PHP dependencies
- Generate application key
- Run database migrations

### Manual Installation

1. Clone the repository and navigate to the project directory

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Build and start the Docker containers:
```bash
docker-compose up -d --build
```

4. Install PHP dependencies:
```bash
docker-compose exec app composer install
```

5. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

6. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

7. Install Node.js dependencies and build assets:
```bash
npm install
npm run build
```

For development with hot reload:
```bash
npm run dev
```

## Usage

- Access the application at: http://localhost:8000
- API endpoints are available at: http://localhost:8000/api/v1/profiles

## API Endpoints

- `GET /api/v1/profiles` - List all profiles
- `POST /api/v1/profiles` - Create a new profile
- `GET /api/v1/profiles/{id}` - Get a specific profile
- `PUT /api/v1/profiles/{id}` - Update a profile
- `DELETE /api/v1/profiles/{id}` - Delete a profile
- `GET /api/v1/profiles/{id}/public` - Get public profile information

## Docker Commands

### Using Makefile (Recommended)

- Full installation: `make install`
- Build containers: `make build`
- Start containers: `make up`
- Stop containers: `make down`
- View logs: `make logs`
- Access shell: `make shell`
- Run migrations: `make migrate`
- Generate key: `make key`
- Install dependencies: `make composer`

### Using Docker Compose Directly

- Start containers: `docker-compose up -d`
- Stop containers: `docker-compose down`
- View logs: `docker-compose logs -f app`
- Execute commands: `docker-compose exec app php artisan [command]`

## Development

The application uses:
- **Laravel 11** - PHP framework
- **FrankenPHP** - Modern PHP application server
- **MySQL 8.0** - Database
- **Tailwind CSS** - Styling
- **jQuery** - JavaScript library for API interactions
- **Vite** - Asset bundler
