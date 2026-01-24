#!/bin/bash

# GitHub Setup Script for Profile Site
# This script helps you push the project to GitHub

set -e

echo "🚀 GitHub Setup Script"
echo "======================"
echo ""

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "❌ Git repository not initialized. Run 'git init' first."
    exit 1
fi

# Check if remote already exists
if git remote get-url origin > /dev/null 2>&1; then
    echo "⚠️  Remote 'origin' already exists:"
    git remote get-url origin
    read -p "Do you want to update it? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "Enter your GitHub repository URL (e.g., https://github.com/username/repo.git): " REPO_URL
        git remote set-url origin "$REPO_URL"
        echo "✅ Remote updated!"
    else
        echo "Keeping existing remote."
    fi
else
    read -p "Enter your GitHub repository URL (e.g., https://github.com/username/repo.git): " REPO_URL
    git remote add origin "$REPO_URL"
    echo "✅ Remote added!"
fi

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
echo ""
echo "Current branch: $CURRENT_BRANCH"

# Ask if user wants to push
read -p "Do you want to push to GitHub now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "📤 Pushing to GitHub..."
    git push -u origin "$CURRENT_BRANCH"
    echo ""
    echo "✅ Successfully pushed to GitHub!"
    echo ""
    echo "Your repository is now available on GitHub!"
    echo "You can open it in VS Code Dev Containers by:"
    echo "  1. Opening the repository in VS Code"
    echo "  2. Clicking 'Reopen in Container' when prompted"
    echo ""
else
    echo ""
    echo "To push manually, run:"
    echo "  git push -u origin $CURRENT_BRANCH"
    echo ""
fi
