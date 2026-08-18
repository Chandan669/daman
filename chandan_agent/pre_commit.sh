#!/bin/bash
set -e
echo "Running Pre-commit Instructions..."
npm run test
cd frontend
npm run build
cd ..
echo "All verifications passed successfully!"
