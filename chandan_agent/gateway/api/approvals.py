from fastapi import APIRouter, Depends
from models.database import SessionLocal, ApprovalRecord, TaskRecord, TaskStatus

router = APIRouter()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@router.get("/")
def list_approvals(db = Depends(get_db)):
    return db.query(ApprovalRecord).filter(ApprovalRecord.status == "PENDING").all()

@router.post("/{task_id}")
async def resolve_approval(task_id: int, approved: bool, db = Depends(get_db)):
    task = db.query(TaskRecord).filter(TaskRecord.id == task_id).first()
    if not task:
        return {"error": "Task not found"}

    approvals = db.query(ApprovalRecord).filter(ApprovalRecord.task_id == task_id, ApprovalRecord.status == "PENDING").all()
    for app in approvals:
        app.status = "APPROVED" if approved else "REJECTED"

    if approved:
        task.status = TaskStatus.RUNNING
    else:
        task.status = TaskStatus.CANCELLED
    db.commit()

    from api.ws import manager
    for device_id in manager.active_connections:
        await manager.send_personal_message({"type": "approval_resolved", "task_id": task_id, "approved": approved}, device_id)

    return {"status": "success"}
