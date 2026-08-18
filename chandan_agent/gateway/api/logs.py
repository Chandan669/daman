from fastapi import APIRouter, Depends
from models.database import SessionLocal, LogRecord

router = APIRouter()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@router.get("/")
def get_logs(db = Depends(get_db)):
    return db.query(LogRecord).order_by(LogRecord.timestamp.desc()).limit(50).all()
