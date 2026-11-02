#!/bin/bash
set -euo pipefail

export HOME="${HOME:-/var/www}"

mkdir -p "$HOME/.config" "$HOME/.ssh"

# --- Git identity (writable copy; never mutate host bind mounts) ---
HOST_GITCONFIG=""
if [ -f /tmp/host-gitconfig ]; then
    HOST_GITCONFIG="/tmp/host-gitconfig"
elif [ -f /tmp/host-gitconfig/.gitconfig ]; then
    HOST_GITCONFIG="/tmp/host-gitconfig/.gitconfig"
fi

if [ -n "$HOST_GITCONFIG" ]; then
    cp "$HOST_GITCONFIG" "$HOME/.gitconfig"
elif [ ! -f "$HOME/.gitconfig" ]; then
    cat > "$HOME/.gitconfig" <<'EOF'
[user]
	name = synysmike
	email = sembuarang@yahoo.com
EOF
fi

NAME="$(git config --global --get user.name 2>/dev/null || true)"
EMAIL="$(git config --global --get user.email 2>/dev/null || true)"
[ -n "$NAME" ] || git config --global user.name "synysmike"
[ -n "$EMAIL" ] || git config --global user.email "sembuarang@yahoo.com"

# Credential helper via gh for HTTPS push/pull
git config --global --unset-all credential.https://github.com.helper 2>/dev/null || true
git config --global --add credential.https://github.com.helper ""
git config --global --add credential.https://github.com.helper "!/usr/bin/gh auth git-credential"
git config --global --unset-all credential.https://gist.github.com.helper 2>/dev/null || true
git config --global --add credential.https://gist.github.com.helper ""
git config --global --add credential.https://gist.github.com.helper "!/usr/bin/gh auth git-credential"

git config --global --unset-all safe.directory 2>/dev/null || true
git config --global --add safe.directory /var/www/html
git config --global --add safe.directory '*'

# --- GitHub CLI config ---
# Host-mounted ~/.config/gh is often mode 600 owned by host UID.
# www-data (UID 33) cannot read it unless Dev Containers remaps UID.
# Always copy into a writable runtime dir owned by the container user.
RUNTIME_GH_DIR="$HOME/.config/gh-runtime"
mkdir -p "$RUNTIME_GH_DIR"

HOST_GH_SRC=""
if [ -f /tmp/host-gh-config/hosts.yml ]; then
    HOST_GH_SRC="/tmp/host-gh-config"
elif [ -f "$HOME/.config/gh/hosts.yml" ]; then
    HOST_GH_SRC="$HOME/.config/gh"
fi

if [ -n "$HOST_GH_SRC" ]; then
    # Prefer reading via root-copied /tmp mounts; fall back to mounted path
    if [ -r "$HOST_GH_SRC/hosts.yml" ]; then
        cp -a "$HOST_GH_SRC/." "$RUNTIME_GH_DIR/" 2>/dev/null || true
    fi
fi

# If still missing (permission denied on mount), try root helper path
if [ ! -f "$RUNTIME_GH_DIR/hosts.yml" ] && [ -f /tmp/host-gh-readable/hosts.yml ]; then
    cp -a /tmp/host-gh-readable/. "$RUNTIME_GH_DIR/" 2>/dev/null || true
fi

chmod -R u+rwX "$RUNTIME_GH_DIR" 2>/dev/null || true
export GH_CONFIG_DIR="$RUNTIME_GH_DIR"

# Persist for interactive shells
PROFILE_SNIPPET="$HOME/.bashrc"
touch "$PROFILE_SNIPPET"
if ! grep -q 'GH_CONFIG_DIR=.*gh-runtime' "$PROFILE_SNIPPET" 2>/dev/null; then
    cat >> "$PROFILE_SNIPPET" <<EOF

# Devcontainer GitHub auth (managed by setup-github.sh)
export HOME=/var/www
export GH_CONFIG_DIR=\$HOME/.config/gh-runtime
EOF
fi

# Also write env file for non-login shells / tooling
cat > "$HOME/.config/gh-env" <<EOF
export HOME=/var/www
export GH_CONFIG_DIR=/var/www/.config/gh-runtime
EOF

echo "Git identity: $(git config --global --get user.name) <$(git config --global --get user.email)>"

if command -v gh >/dev/null 2>&1; then
    if GH_CONFIG_DIR="$RUNTIME_GH_DIR" gh auth status >/dev/null 2>&1; then
        LOGIN="$(GH_CONFIG_DIR="$RUNTIME_GH_DIR" gh api user -q .login 2>/dev/null || echo synysmike)"
        echo "GitHub: authenticated as ${LOGIN}"
    else
        echo "GitHub: belum siap. Pastikan host sudah 'gh auth login', lalu rebuild/recreate container."
        GH_CONFIG_DIR="$RUNTIME_GH_DIR" gh auth status 2>&1 || true
    fi
else
    echo "GitHub CLI (gh) belum ada di image — rebuild container."
fi
