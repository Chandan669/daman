@echo off
echo Starting Chandan Agent Core...
cd backend
start uvicorn main:app --host 0.0.0.0 --port 8000 --reload
echo Starting Frontend Dashboard...
cd ../frontend
start npm run dev
