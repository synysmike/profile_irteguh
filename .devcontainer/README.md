# Dev Container Setup

This project includes a VS Code Dev Container configuration for seamless development.

## Features

- **FrankenPHP** - Modern PHP application server
- **MySQL 8.0** - Database server
- **Git & GitHub CLI** - Pre-installed for version control
- **VS Code Extensions** - Pre-configured for Laravel, Tailwind, and JavaScript development

## Prerequisites

- Docker Desktop (or Docker Engine + Docker Compose)
- Visual Studio Code
- [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)

## Getting Started

1. Open the project in VS Code
2. When prompted, click "Reopen in Container"
   - Or use Command Palette (F1) → "Dev Containers: Reopen in Container"
3. Wait for the container to build and start
4. The workspace will be mounted at `/var/www/html` (only the Laravel project)

## Included Extensions

- **Laravel**: Intelephense, Laravel Extras, Laravel Blade, Laravel Artisan
- **Tailwind CSS**: Tailwind CSS IntelliSense
- **JavaScript**: ESLint, Prettier, TypeScript
- **Git**: GitLens, GitHub Copilot
- **PHP**: PHP Debug, Intelephense

## GitHub Integration

Git and GitHub CLI are pre-installed. To authenticate with GitHub:

```bash
gh auth login
```

Follow the prompts to authenticate with your GitHub account.

## First Time Setup

After opening in the container, run:

```bash
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Accessing Services

- **Laravel App**: http://localhost:8000
- **MySQL**: localhost:3306
  - Database: `profile_db`
  - Username: `profile_user`
  - Password: `profile_password`

## Notes

- The workspace folder is set to `/var/www/html` (only the Laravel project)
- All Laravel files are accessible in the container
- Ports 8000 and 3306 are automatically forwarded
