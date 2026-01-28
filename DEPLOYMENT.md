# Production Deployment Guide

This guide will help you deploy the Profile Site to your production server.

## Prerequisites

- Server with Docker and Docker Compose installed
- Git installed on server
- Domain name (optional, can use IP address)
- Minimum 2GB RAM, 10GB disk space

## Step 1: Server Setup

### Install Docker and Docker Compose

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker compose version
```

## Step 2: Clone Repository

```bash
# Navigate to your preferred directory
cd /opt  # or /var/www or wherever you prefer

# Clone the repository
git clone <your-github-repo-url> profile_irteguh
cd profile_irteguh
```

## Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

**Important Production Settings:**

```env
APP_NAME="Profile Site"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com  # or http://your-server-ip:4240

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=profile_db
DB_USERNAME=profile_user
DB_PASSWORD=CHANGE_THIS_SECURE_PASSWORD
```

**⚠️ Security Checklist:**
- [ ] Change `APP_DEBUG` to `false`
- [ ] Set strong `DB_PASSWORD`
- [ ] Change `DB_USERNAME` if needed
- [ ] Set correct `APP_URL`
- [ ] Generate new `APP_KEY` (will be done in next step)

## Step 4: Update Docker Compose (Optional)

If you want to use environment variables from `.env` file instead of hardcoded values, update `docker-compose.yml`:

```yaml
environment:
  - APP_ENV=${APP_ENV:-production}
  - DB_CONNECTION=${DB_CONNECTION:-mysql}
  - DB_HOST=${DB_HOST:-mysql}
  - DB_PORT=${DB_PORT:-3306}
  - DB_DATABASE=${DB_DATABASE:-profile_db}
  - DB_USERNAME=${DB_USERNAME:-profile_user}
  - DB_PASSWORD=${DB_PASSWORD:-profile_password}
```

## Step 5: Build and Start Containers

```bash
# Build and start containers
docker compose up -d --build

# Check container status
docker compose ps
```

## Step 6: Setup Application

```bash
# Install PHP dependencies (production mode)
docker compose exec app composer install --optimize-autoloader --no-dev

# Generate application key
docker compose exec app php artisan key:generate

# Run database migrations
docker compose exec app php artisan migrate --force

# Install Node.js dependencies
docker compose exec app npm ci

# Build production assets
docker compose exec app npm run build

# Cache configuration for performance
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Step 7: Set Permissions

```bash
# Set proper ownership
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache

# Set proper permissions
docker compose exec app chmod -R 775 /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

## Step 8: Configure Firewall

```bash
# Allow HTTP/HTTPS (if using reverse proxy)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow application port (if direct access)
sudo ufw allow 4240/tcp

# Allow MySQL port (only if needed externally)
# sudo ufw allow 4241/tcp  # NOT RECOMMENDED - keep MySQL internal
```

## Step 9: Setup Reverse Proxy (Recommended)

### Using Nginx

```bash
# Install Nginx
sudo apt install nginx -y

# Create site configuration
sudo nano /etc/nginx/sites-available/profile-site
```

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    location / {
        proxy_pass http://localhost:4240;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/profile-site /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### Using Apache

```bash
# Install Apache
sudo apt install apache2 -y

# Enable required modules
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod headers

# Create virtual host
sudo nano /etc/apache2/sites-available/profile-site.conf
```

**Apache Configuration:**

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    ProxyPreserveHost On
    ProxyPass / http://localhost:4240/
    ProxyPassReverse / http://localhost:4240/

    <Proxy *>
        Order allow,deny
        Allow from all
    </Proxy>
</VirtualHost>
```

```bash
# Enable site
sudo a2ensite profile-site.conf

# Reload Apache
sudo systemctl reload apache2
```

## Step 10: Setup SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y  # For Nginx
# OR
sudo apt install certbot python3-certbot-apache -y  # For Apache

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
# OR
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically
```

## Step 11: Setup Auto-start on Boot

```bash
# Create systemd service
sudo nano /etc/systemd/system/profile-site.service
```

**Service File:**

```ini
[Unit]
Description=Profile Site Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/profile_irteguh
ExecStart=/usr/local/bin/docker compose up -d
ExecStop=/usr/local/bin/docker compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

```bash
# Enable service
sudo systemctl enable profile-site.service
sudo systemctl start profile-site.service
```

## Maintenance Commands

### Update Application

```bash
cd /opt/profile_irteguh

# Pull latest changes
git pull

# Rebuild containers (if Dockerfile changed)
docker compose up -d --build

# Update dependencies
docker compose exec app composer install --optimize-autoloader --no-dev
docker compose exec app npm ci && npm run build

# Run migrations
docker compose exec app php artisan migrate --force

# Clear and rebuild cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Restart application
docker compose restart app
```

### Backup Database

```bash
# Create backup
docker compose exec mysql mysqldump -u profile_user -p profile_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Or with password (less secure)
docker compose exec mysql mysqldump -u profile_user -pprofile_password profile_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database

```bash
# Restore from backup
docker compose exec -T mysql mysql -u profile_user -pprofile_password profile_db < backup.sql
```

### View Logs

```bash
# Application logs
docker compose logs -f app

# MySQL logs
docker compose logs -f mysql

# All logs
docker compose logs -f
```

### Monitor Resources

```bash
# Container stats
docker stats

# Disk usage
docker system df
```

## Troubleshooting

### Application Not Accessible

```bash
# Check containers are running
docker compose ps

# Check logs
docker compose logs app

# Check port is open
sudo netstat -tlnp | grep 4240
```

### Database Connection Issues

```bash
# Check MySQL is running
docker compose ps mysql

# Test connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Errors

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/storage
```

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] Firewall configured
- [ ] MySQL port (4241) not exposed externally
- [ ] SSL certificate installed
- [ ] Regular backups configured
- [ ] Application key generated
- [ ] File permissions set correctly

## Support

For issues, check:
- Application logs: `docker compose logs app`
- Server logs: `journalctl -u profile-site`
- [README.md](README.md) for general documentation
