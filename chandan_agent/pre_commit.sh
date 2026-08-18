#!/bin/bash
set -e
echo "Running Pre-commit Instructions..."
export AGENT_SECRET="test_secret"
export ALLOW_DEV_STARTUP="true"
python -m pytest gateway/test_gateway.py
cd frontend
npm run build
cd ..
echo "All verifications passed successfully!"
