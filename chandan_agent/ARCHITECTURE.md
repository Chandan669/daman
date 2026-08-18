# Architecture

Chandan Agent consists of a Python/FastAPI backend running locally and a React/Vite frontend.

## Components
1. **Local Agent Core:** Background service.
2. **FastAPI Gateway:** Secure websocket/REST gateway.
3. **Frontend:** React + Vite, Mobile-first.
4. **AI Orchestrator:** Natural language to task planner.
5. **Tool Registry:** Pre-defined tools for the agent to use safely.
6. **Permission Manager:** 4-level permission system.
