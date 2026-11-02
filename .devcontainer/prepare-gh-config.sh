#!/bin/bash
# Run as root before setup-github.sh so www-data can read host gh credentials.
set -euo pipefail

SRC=""
if [ -d /tmp/host-gh-config ] && [ -f /tmp/host-gh-config/hosts.yml ]; then
    SRC=/tmp/host-gh-config
elif [ -d /var/www/.config/gh ] && [ -f /var/www/.config/gh/hosts.yml ]; then
    SRC=/var/www/.config/gh
fi

if [ -n "$SRC" ]; then
    mkdir -p /tmp/host-gh-readable
    cp -a "$SRC/." /tmp/host-gh-readable/
    chmod -R a+rX /tmp/host-gh-readable
    echo "Prepared readable gh config at /tmp/host-gh-readable"
else
    echo "No host gh config found to prepare"
fi
