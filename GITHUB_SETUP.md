# GitHub Setup Instructions

## Step 1: Create GitHub Repository

1. Go to [GitHub](https://github.com) and sign in
2. Click the "+" icon in the top right → "New repository"
3. Name your repository (e.g., `profile_irteguh`)
4. Choose public or private
5. **DO NOT** initialize with README, .gitignore, or license (we already have these)
6. Click "Create repository"

## Step 2: Push to GitHub

### Option A: Using the Setup Script

```bash
./scripts/setup-github.sh
```

Follow the prompts to enter your GitHub repository URL and push.

### Option B: Manual Setup

1. Add your GitHub repository as remote:
```bash
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
```

2. Push to GitHub:
```bash
git push -u origin main
```

If your default branch is `master` instead of `main`:
```bash
git push -u origin master
```

## Step 3: Using Dev Containers

After pushing to GitHub:

1. **Clone the repository** (if working on a different machine):
   ```bash
   git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   cd YOUR_REPO_NAME
   ```

2. **Open in VS Code**:
   ```bash
   code .
   ```

3. **Open in Dev Container**:
   - VS Code will prompt: "Folder contains a Dev Container configuration file. Reopen folder to develop in a container"
   - Click **"Reopen in Container"**
   - Or use Command Palette (F1) → "Dev Containers: Reopen in Container"

4. **Authenticate with GitHub** (inside the container):
   ```bash
   gh auth login
   ```
   Follow the prompts to authenticate.

5. **First-time setup** (inside the container):
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

## Dev Container Features

- ✅ **Git & GitHub CLI** pre-installed
- ✅ **Laravel extensions** configured
- ✅ **Tailwind CSS** IntelliSense
- ✅ **JavaScript/jQuery** support with ESLint & Prettier
- ✅ **PHP debugging** ready
- ✅ **Workspace**: Only the Laravel project is mounted at `/var/www/html`

## Troubleshooting

### Dev Container won't start
- Ensure Docker Desktop is running
- Check Docker has enough resources allocated (4GB RAM recommended)
- Try rebuilding: Command Palette → "Dev Containers: Rebuild Container"

### GitHub authentication issues
- Run `gh auth login` inside the container
- For SSH keys, ensure they're added to your GitHub account

### Port conflicts
- If port 8000 is in use, change it in `.devcontainer/docker-compose.yml`
- If port 3306 is in use, change the MySQL port mapping

## Next Steps

- Start developing! The app will be available at http://localhost:8000
- Make changes and commit: `git add . && git commit -m "Your message"`
- Push changes: `git push`
