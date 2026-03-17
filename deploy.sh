#!/bin/bash

# ==========================================================
# PSSM Automated Deployment Script
# ==========================================================
# This script automates the deployment process for the production environment.
# Ensure it is run as the user owning the web directory.

# Stop upon error
set -e

echo "🚀 Starting PSSM Deployment Process..."

# 1. Enter maintenance mode or return true if already in maintenance mode
echo "⏳ Entering maintenance mode..."
(php artisan down --render="errors::503" --secret="pssm-deploy-bypass") || true

# 2. Pull the latest changes from the git repository
echo "📥 Pulling latest code..."
git pull origin main

# 3. Install/update composer dependencies (optimizing for production)
echo "📦 Installing/Updating composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 4. Install/update NPM dependencies and build frontend assets
echo "🎨 Building frontend assets..."
npm ci
npm run build

# 5. Run database migrations (forcing execution without interaction in production)
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Clear and recache Laravel configurations, routes, and views
echo "🧹 Optimizing Laravel caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart queues or horizon (if using Supervisor/Horizon)
echo "🔄 Restarting queue workers..."
# If using Horizon:
php artisan horizon:terminate
# If not using Horizon, uncomment the following line and configure your queue:
# php artisan queue:restart

# 8. Exit maintenance mode
echo "✅ Bringing application out of maintenance mode..."
php artisan up

echo "🎉 Deployment completed successfully!"
