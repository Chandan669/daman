#!/bin/bash

# Start the license server on 8001
echo "Starting License Server on port 8001..."
cd /app/license-server && php -S localhost:8001 > /tmp/license_server.log 2>&1 &

# Start the main application on 8000
echo "Starting Ghost News & Magazine on port 8000..."
cd /app && php -S localhost:8000 > /tmp/php_server.log 2>&1 &

echo "Servers are running!"
echo "Main Application: http://localhost:8000"
echo "License Server: http://localhost:8001"
