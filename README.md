# Profile Site - Laravel 11 with FrankenPHP

A modern profile management application built with Laravel 11, FrankenPHP, MySQL, Tailwind CSS, and jQuery.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Installation](#installation)
- [Development](#development)
- [Production Deployment](#production-deployment)
- [API Documentation](#api-documentation)
- [Docker Commands](#docker-commands)
- [Troubleshooting](#troubleshooting)

## ✨ Features

- **Laravel 11** - Modern PHP framework
- **FrankenPHP** - High-performance PHP application server with built-in Caddy
- **MySQL 8.0** - Reliable database
- **Tailwind CSS** - Utility-first CSS framework
- **jQuery** - JavaScript library for API interactions
- **Vite** - Fast asset bundler
- **RESTful API** - Complete CRUD operations for profiles
- **Dev Containers** - VS Code development environment
- **Docker** - Containerized deployment

## 📦 Requirements

- **Docker** 20.10+ and **Docker Compose** 2.0+
- **Git** (for cloning)
- **Node.js 20+** and **npm** (for building assets locally, optional)

## 🚀 Quick Start

### Using Dev Containers (Recommended for Development)

1. Open the project in VS Code
2. Click "Reopen in Container" when prompted
3. Wait for the container to build
4. Access the application at http://localhost:4240

### Using Docker Compose

```bash
# Clone the repository
git clone <your-repo-url>
cd profile_irteguh

# Copy environment file
cp .env.example .env

# Start containers
docker compose up -d --build

# Install dependencies and setup
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate

# Build frontend assets
docker compose exec app npm install
docker compose exec app npm run build

# Access the application
# http://localhost:4240
```

## 📖 Installation

### Step 1: Clone Repository

```bash
git clone <your-repo-url>
cd profile_irteguh
```

### Step 2: Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` file and configure:
- Database credentials
- Application URL
- Other environment variables as needed

### Step 3: Start Docker Containers

```bash
docker compose up -d --build
```

### Step 4: Install Dependencies

```bash
# PHP dependencies
docker compose exec app composer install

# Generate application key
docker compose exec app php artisan key:generate

# Run database migrations
docker compose exec app php artisan migrate

# Node.js dependencies and build assets
docker compose exec app npm install
docker compose exec app npm run build
```

### Step 5: Access Application

- **Web Interface**: http://localhost:4240
- **API**: http://localhost:4240/api/v1/profiles

## 🛠️ Development

### Using Dev Containers

The project includes VS Code Dev Container configuration:

1. Open in VS Code
2. Command Palette → "Dev Containers: Reopen in Container"
3. All dependencies are installed automatically

See [.devcontainer/README.md](.devcontainer/README.md) for details.

### Local Development

```bash
# Start containers
docker compose up -d

# Access container shell
docker compose exec app bash

# Run migrations
php artisan migrate

# Build assets with hot reload
npm run dev

# Run tests
php artisan test
```

### Project Structure

```
profile_irteguh/
├── app/                    # Laravel application
│   ├── Http/
│   │   └── Controllers/   # API and web controllers
│   └── Models/             # Eloquent models
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── views/             # Blade templates
│   ├── css/               # Tailwind CSS
│   └── js/                # JavaScript/jQuery
├── routes/
│   ├── api.php            # API routes
│   └── web.php            # Web routes
├── docker-compose.yml     # Docker services
├── Dockerfile             # Application container
└── Caddyfile              # FrankenPHP configuration
```

## 🚢 Production Deployment

### Server Requirements

- Docker 20.10+ and Docker Compose 2.0+
- Minimum 2GB RAM
- 10GB disk space
- Ports 4240 (app) and 4241 (MySQL) available

### Deployment Steps

1. **Clone on Server**

```bash
git clone <your-repo-url>
cd profile_irteguh
```

2. **Configure Environment**

```bash
cp .env.example .env
nano .env  # Edit with production values
```

**Important Environment Variables:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password
```

3. **Build and Start Containers**

```bash
docker compose up -d --build
```

4. **Setup Application**

```bash
# Install dependencies
docker compose exec app composer install --optimize-autoloader --no-dev

# Generate key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate --force

# Build production assets
docker compose exec app npm ci
docker compose exec app npm run build

# Cache configuration
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

5. **Configure Reverse Proxy (Optional)**

If using a reverse proxy (nginx/Apache) in front:

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://localhost:4240;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

6. **Set Permissions**

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

### Production Maintenance

```bash
# View logs
docker compose logs -f app

# Update application
git pull
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app php artisan migrate --force
docker compose exec app npm ci && npm run build
docker compose exec app php artisan config:cache
docker compose restart app

# Backup database
docker compose exec mysql mysqldump -u profile_user -pprofile_password profile_db > backup.sql

# Restore database
docker compose exec -T mysql mysql -u profile_user -pprofile_password profile_db < backup.sql
```

## 📡 API Documentation

### Base URL
```
http://localhost:4240/api/v1
```

### Endpoints

#### List All Profiles
```http
GET /api/v1/profiles
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "bio": "Software Developer",
      "phone": "+1234567890",
      "location": "New York",
      "website": "https://johndoe.com",
      "avatar": "https://example.com/avatar.jpg",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

#### Create Profile
```http
POST /api/v1/profiles
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "bio": "Designer",
  "phone": "+1234567890",
  "location": "San Francisco",
  "website": "https://janedoe.com",
  "avatar": "https://example.com/avatar.jpg"
}
```

#### Get Profile
```http
GET /api/v1/profiles/{id}
```

#### Update Profile
```http
PUT /api/v1/profiles/{id}
Content-Type: application/json
```

#### Delete Profile
```http
DELETE /api/v1/profiles/{id}
```

#### Get Public Profile
```http
GET /api/v1/profiles/{id}/public
```

Returns limited profile information (excludes email and phone).

## 🐳 Docker Commands

### Using Makefile

```bash
make install      # Full installation
make build       # Build containers
make up          # Start containers
make down        # Stop containers
make logs        # View logs
make shell       # Access container shell
make migrate     # Run migrations
make key         # Generate app key
make composer    # Install PHP dependencies
```

### Using Docker Compose

```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# View logs
docker compose logs -f app

# Execute commands
docker compose exec app php artisan [command]
docker compose exec app composer [command]
docker compose exec app npm [command]

# Rebuild containers
docker compose up -d --build

# View running containers
docker compose ps
```

## 🔧 Configuration

### Ports

- **4240** - Application (FrankenPHP)
- **4241** - MySQL Database

### Environment Variables

Key variables in `.env`:

```env
APP_NAME=ProfileSite
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:4240

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=profile_db
DB_USERNAME=profile_user
DB_PASSWORD=profile_password
```

### Database

Default MySQL credentials:
- **Database**: `profile_db`
- **Username**: `profile_user`
- **Password**: `profile_password`
- **Root Password**: `root_password`

**⚠️ Change these in production!**

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Check what's using the port
sudo lsof -i :4240
sudo lsof -i :4241

# Stop conflicting containers
docker compose down
```

### Container Won't Start

```bash
# Check logs
docker compose logs app

# Rebuild containers
docker compose down
docker compose up -d --build
```

### Database Connection Error

```bash
# Check MySQL is running
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Test connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Assets Not Loading

```bash
# Rebuild assets
docker compose exec app npm run build

# Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
```

### Permission Errors

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/storage
```

## 📝 License

This project is open-sourced software.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For issues and questions:
- Open an issue on GitHub
- Check [TROUBLESHOOTING.md](.devcontainer/TROUBLESHOOTING.md)

---

**Built with ❤️ using Laravel 11 and FrankenPHP**
