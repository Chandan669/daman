from fastapi import APIRouter, Depends
from models.database import SessionLocal, TaskRecord, TaskStatus

router = APIRouter()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@router.post("/")
async def create_task(command: str, db = Depends(get_db)):
    task = TaskRecord(command=command, status=TaskStatus.QUEUED)
    db.add(task)
    db.commit()
    db.refresh(task)
    # Forward task to agent via WebSocket here...
    from api.ws import manager
    for device_id in manager.active_connections:
        await manager.send_personal_message({"type": "new_task", "task_id": task.id, "command": command}, device_id)
    return {"task_id": task.id, "status": "QUEUED"}

@router.get("/")
def get_tasks(db = Depends(get_db)):
    return db.query(TaskRecord).order_by(TaskRecord.created_at.desc()).limit(20).all()

@router.post("/stop")
async def emergency_stop(db = Depends(get_db)):
    from api.ws import manager
    tasks = db.query(TaskRecord).filter(TaskRecord.status.in_([TaskStatus.QUEUED, TaskStatus.PLANNING, TaskStatus.WAITING_APPROVAL])).all()
    for t in tasks:
        t.status = TaskStatus.CANCELLED
    db.commit()

    for device_id in manager.active_connections:
        await manager.send_personal_message({"type": "emergency_stop"}, device_id)
    return {"status": "stopped"}
