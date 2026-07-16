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

Akun Git/GitHub dari host **otomatis diteruskan** ke container:

| Dari host | Di container |
|-----------|--------------|
| `~/.gitconfig` (name/email) | disalin ke `/var/www/.gitconfig` |
| `~/.config/gh` (token `gh auth`) | disalin ke `/var/www/.config/gh-runtime` |

DNS publik (`8.8.8.8` / `1.1.1.1`) juga diset agar `git push` / `gh` bisa ke GitHub.

### Prasyarat di host

```bash
gh auth login
git config --global user.name "synysmike"
git config --global user.email "sembuarang@yahoo.com"
```

### Push dari dalam container

Setelah **Rebuild Container** (atau recreate compose):

```bash
gh auth status          # harus: Logged in as synysmike
git config --global --get user.name
git push
```

Jika auth gagal: pastikan host masih `gh auth login`, lalu rebuild.

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

- **Laravel App**: http://localhost:4240
- **MySQL**: localhost:4241
  - Database: `profile_db`
  - Username: `profile_user`
  - Password: `profile_password`

## Notes

- The workspace folder is set to `/var/www/html` (only the Laravel project)
- All Laravel files are accessible in the container
- Ports 4240 (app) and 4241 (MySQL) are automatically forwarded
