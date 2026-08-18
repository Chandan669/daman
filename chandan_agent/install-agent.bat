@echo off
echo Installing Windows Agent Dependencies...
cd windows_agent
python -m pip install -r requirements.txt
python -m playwright install
echo Done.
