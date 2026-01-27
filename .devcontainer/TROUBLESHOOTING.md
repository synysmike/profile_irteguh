# Dev Container Troubleshooting

## Common Issues and Solutions

### Issue: Container fails to start / "container is not running"

**Symptoms:**

- Error: `Failed to start Dev Container: container is not running`
- Container keeps restarting

**Solutions:**

1. **Rebuild the container:**
    - Command Palette (F1) → "Dev Containers: Rebuild Container"
    - This will rebuild with the latest configuration

2. **Check Docker logs:**

    ```bash
    docker logs profile_app_dev
    docker logs profile_mysql_dev
    ```

3. **Clean up and restart:**

    ```bash
    docker-compose -f .devcontainer/docker-compose.yml down
    docker-compose -f .devcontainer/docker-compose.yml up -d
    ```

4. **Check port conflicts:**
    - Ensure ports 8000 and 3306 are not in use
    - Change ports in `.devcontainer/docker-compose.yml` if needed

### Issue: MySQL connection errors

**Symptoms:**

- Laravel can't connect to MySQL
- Database errors in logs

**Solutions:**

1. **Wait for MySQL to be ready:**
    - The container includes a healthcheck
    - Wait 10-15 seconds after container starts

2. **Check MySQL is running:**

    ```bash
    docker ps | grep mysql
    ```

3. **Test connection:**
    ```bash
    docker exec -it profile_mysql_dev mysql -u profile_user -pprofile_password profile_db
    ```

### Issue: FrankenPHP not starting

**Symptoms:**

- Error: `unknown flag: --worker`
- Container exits immediately

**Solution:**

- Fixed in latest version - FrankenPHP uses `frankenphp run` without flags
- Rebuild the container to get the fix

### Issue: Port already in use

**Symptoms:**

- Error binding to port 8000 or 3306

**Solutions:**

1. **Change ports in `.devcontainer/docker-compose.yml`:**

    ```yaml
    ports:
        - "8001:8000" # Change 8000 to 8001
        - "3307:3306" # Change 3306 to 3307
    ```

2. **Stop conflicting containers:**
    ```bash
    docker ps
    docker stop <container_id>
    ```

### Issue: VS Code extensions not loading

**Symptoms:**

- Extensions listed but not working

**Solutions:**

1. **Reload window:**
    - Command Palette → "Developer: Reload Window"

2. **Install manually:**
    - Extensions view → Search and install manually

3. **Check extension compatibility:**
    - Some extensions may not work in containers
    - Check extension documentation

### Issue: Git/GitHub authentication

**Symptoms:**

- Can't push to GitHub
- Authentication errors

**Solutions:**

1. **Authenticate GitHub CLI:**

    ```bash
    gh auth login
    ```

2. **Configure Git:**

    ```bash
    git config --global user.name "Your Name"
    git config --global user.email "your.email@example.com"
    ```

3. **Use SSH keys:**
    - Add SSH key to GitHub
    - Use SSH URL for remote: `git@github.com:user/repo.git`

### Issue: Permission errors

**Symptoms:**

- Can't write to files
- Permission denied errors

**Solutions:**

1. **Fix ownership:**

    ```bash
    sudo chown -R www-data:www-data /var/www/html
    ```

2. **Fix permissions:**
    ```bash
    sudo chmod -R 755 /var/www/html
    sudo chmod -R 775 storage bootstrap/cache
    ```

### Issue: Composer/Artisan commands fail

**Symptoms:**

- `composer install` fails
- `php artisan` commands don't work

**Solutions:**

1. **Run inside container:**
    - All commands should run inside the devcontainer
    - Use integrated terminal in VS Code

2. **Check PHP version:**

    ```bash
    php -v
    ```

3. **Clear cache:**
    ```bash
    composer clear-cache
    php artisan config:clear
    php artisan cache:clear
    ```

## Getting Help

If issues persist:

1. Check Docker Desktop is running
2. Ensure sufficient resources (4GB RAM, 2 CPU cores)
3. Check VS Code Dev Containers extension is installed
4. Review container logs: `docker logs profile_app_dev`
5. Try rebuilding: Command Palette → "Dev Containers: Rebuild Container"

## Useful Commands

```bash
# View running containers
docker ps

# View container logs
docker logs profile_app_dev -f

# Access container shell
docker exec -it profile_app_dev bash

# Rebuild devcontainer
docker-compose -f .devcontainer/docker-compose.yml build --no-cache

# Stop all containers
docker-compose -f .devcontainer/docker-compose.yml down

# Start containers
docker-compose -f .devcontainer/docker-compose.yml up -d
```
