from fastapi import APIRouter, Depends
from models.database import SessionLocal, DevicePairing

router = APIRouter()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@router.get("/")
def list_devices(db = Depends(get_db)):
    devices = db.query(DevicePairing).all()
    return [{"device_id": d.device_id, "is_active": d.is_active, "last_heartbeat": d.last_heartbeat, "os": d.os_info, "cpu": d.cpu_usage, "ram": d.ram_usage} for d in devices]
