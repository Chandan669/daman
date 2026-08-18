@echo off
echo Stopping Windows Agent processes...
taskkill /F /IM "python.exe" /T
echo Agent stopped.
