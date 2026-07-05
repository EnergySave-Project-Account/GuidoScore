#!/bin/bash

# Install PHP dependencies if composer.json exists
if [ -f "composer.json" ]; then
    composer install
fi

# Start PHP built-in server
php -S 0.0.0.0:${PORT:-8080} -t public/
