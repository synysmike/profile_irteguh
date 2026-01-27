#!/bin/bash
# Cleanup script to stop conflicting containers before starting devcontainer

echo "Cleaning up existing containers..."
docker stop profile_app profile_web profile_mysql profile_app_dev profile_mysql_dev 2>/dev/null
docker rm profile_app profile_web profile_mysql profile_app_dev profile_mysql_dev 2>/dev/null
echo "Cleanup complete!"
