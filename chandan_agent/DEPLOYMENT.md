# Deployment Guide

Chandan Agent consists of two separate physical deployments:
1. **Remote Gateway & Web Dashboard** (Deployed to VPS/Cloud)
2. **Local Windows Agent** (Runs on your Laptop)

## A. agent.bazrio.com Frontend
- Deploy the React/Vite app located in `/frontend/dist` to your static web host.
- Set `VITE_AGENT_API_URL=https://www.agent.bazrio.com/api` and `VITE_AGENT_WS_URL=wss://www.agent.bazrio.com/ws/agent`.

## B. Backend Gateway
- Must be deployed on a VPS or cloud service (e.g., Railway, Heroku, AWS) supporting long-lived WebSocket connections. **Shared PHP hosting will NOT work.**
- Set `AGENT_SECRET` to a random secure string.
- Set `ALLOW_DEV_STARTUP=false`.

## C. Windows Agent
- Clone repo to laptop. Run `install-agent.bat`.
- Run `python pair_agent.py` to pair device.
- Run `start-agent.bat`. Keep this running in the background.

## D. DNS & E. HTTPS
- Point `www.agent.bazrio.com` and `agent.bazrio.com` to your VPS.
- Ensure TLS/SSL is enforced.

## Security Warning
Never place your Google, Meta, or Windows credentials into the Gateway's environment variables. They belong ONLY on your local laptop inside `windows_agent/.env`.
