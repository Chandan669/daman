from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware
from api.auth import router as auth_router, verify_token
from api.devices import router as devices_router
from api.tasks import router as tasks_router
from api.approvals import router as approvals_router
from api.logs import router as logs_router
from api.ws import router as ws_router
import os
import sys

# Production safety check
if not os.getenv("AGENT_SECRET") or os.getenv("AGENT_SECRET") == "dev-mode":
    print("CRITICAL: AGENT_SECRET not configured for production. Exiting.")
    if os.getenv("ALLOW_DEV_STARTUP") != "true":
        sys.exit(1)

app = FastAPI(title="Chandan Agent Gateway")

allowed_origins = [
    "https://www.agent.bazrio.com",
    "https://agent.bazrio.com"
]
if os.getenv("ALLOW_DEV_STARTUP") == "true":
    allowed_origins.extend(["http://localhost:5173", "http://127.0.0.1:5173"])

app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(auth_router, prefix="/api/auth", tags=["auth"])
app.include_router(ws_router, prefix="/ws", tags=["websocket"])

# Protected API routes
app.include_router(devices_router, prefix="/api/devices", dependencies=[Depends(verify_token)], tags=["devices"])
app.include_router(tasks_router, prefix="/api/tasks", dependencies=[Depends(verify_token)], tags=["tasks"])
app.include_router(approvals_router, prefix="/api/approvals", dependencies=[Depends(verify_token)], tags=["approvals"])
app.include_router(logs_router, prefix="/api/logs", dependencies=[Depends(verify_token)], tags=["logs"])

@app.get("/api/system/health")
def health_check():
    return {"status": "Gateway Online"}
