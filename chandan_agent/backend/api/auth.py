from fastapi import APIRouter, HTTPException, Depends, Security
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from models.database import SessionLocal, DevicePairing
import hashlib
import os

router = APIRouter()
security = HTTPBearer(auto_error=False)

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

def hash_token(token: str) -> str:
    return hashlib.sha256(token.encode()).hexdigest()

def verify_token(credentials: HTTPAuthorizationCredentials = Security(security), db = Depends(get_db)):
    if os.getenv("AGENT_SECRET") == "dev-mode":
        return True # Bypass in dev

    if not credentials:
        raise HTTPException(status_code=401, detail="Not authenticated")

    token = credentials.credentials
    hashed = hash_token(token)
    device = db.query(DevicePairing).filter(DevicePairing.token_hash == hashed, DevicePairing.is_active == True).first()
    if not device:
        raise HTTPException(status_code=401, detail="Invalid or expired token")
    return device

@router.post("/pair")
def pair_device(device_id: str, db = Depends(get_db)):
    raw_token = os.urandom(24).hex()
    db.add(DevicePairing(device_id=device_id, token_hash=hash_token(raw_token)))
    db.commit()
    return {"status": "paired", "token": raw_token}
