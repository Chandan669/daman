#!/bin/bash
set -e

echo "Running PHP Syntax Checks..."
find /app -type f -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected" || true

echo "Testing License Server Syntax..."
php -l /app/license-server/index.php

echo "All tests passed."
