from fastapi import APIRouter, HTTPException, Depends
from core.orchestrator import AIOrchestrator
from tools.registry import registry
from core.permissions import PermissionManager
from core.ai_provider import DummyAIProvider
from models.database import SessionLocal, TaskRecord, ApprovalRecord, LogRecord

router = APIRouter()
pm = PermissionManager()
ai_provider = DummyAIProvider() # Can be switched via config
orchestrator = AIOrchestrator(registry, pm, ai_provider)

# Simple dependency for database
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@router.post("/command")
def submit_command(command: str):
    return orchestrator.submit_command(command)

@router.post("/stop")
def stop_agent():
    return orchestrator.emergency_stop()

@router.post("/reset-stop")
def reset_stop():
    return orchestrator.reset_stop()

@router.get("/tasks")
def list_tasks(db = Depends(get_db)):
    tasks = db.query(TaskRecord).order_by(TaskRecord.created_at.desc()).limit(20).all()
    return tasks

@router.get("/approvals")
def list_approvals(db = Depends(get_db)):
    approvals = db.query(ApprovalRecord).filter(ApprovalRecord.status == "PENDING").all()
    return approvals

@router.post("/approvals/{task_id}")
def resolve_approval(task_id: int, approved: bool):
    return orchestrator.approve_task(task_id, approved)

@router.get("/logs")
def list_logs(db = Depends(get_db)):
    logs = db.query(LogRecord).order_by(LogRecord.timestamp.desc()).limit(50).all()
    return logs
