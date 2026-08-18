@echo off
echo Building Frontend for Production (agent.bazrio.com)...
cd frontend
call npm install
call npm run build
echo Build Complete!
echo You can now upload the contents of "chandan_agent\frontend\dist" to your web hosting (e.g., Hostinger, cPanel, Vercel).
