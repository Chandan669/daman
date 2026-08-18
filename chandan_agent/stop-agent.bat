@echo off
echo Stopping Chandan Agent processes...
taskkill /F /IM "node.exe" /T
taskkill /F /IM "python.exe" /T
echo Agent stopped.
