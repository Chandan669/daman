from fastapi import APIRouter, HTTPException, Depends, Security
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from models.database import SessionLocal, DevicePairing
import hashlib
import os
import secrets

router = APIRouter()
security = HTTPBearer()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

def hash_token(token: str) -> str:
    return hashlib.sha256(token.encode()).hexdigest()

def verify_token(credentials: HTTPAuthorizationCredentials = Security(security), db = Depends(get_db)):
    if not credentials:
        raise HTTPException(status_code=401, detail="Not authenticated")
    token = credentials.credentials
    hashed = hash_token(token)
    device = db.query(DevicePairing).filter(DevicePairing.token_hash == hashed, DevicePairing.is_active == True).first()
    if not device:
        raise HTTPException(status_code=401, detail="Invalid or expired token")
    return device

@router.post("/generate-pairing")
def generate_pairing_code(db = Depends(get_db)):
    # Called by web dashboard to start pairing
    code = secrets.token_hex(4).upper()
    return {"pairing_code": code}

@router.post("/pair-agent")
def pair_agent(device_id: str, pairing_code: str, db = Depends(get_db)):
    # Called by local Windows agent to finalize pairing
    # In production, require actual UI confirmation. Simplified for demo.
    raw_token = secrets.token_hex(32)
    db.add(DevicePairing(device_id=device_id, token_hash=hash_token(raw_token), pairing_code=pairing_code))
    db.commit()
    return {"status": "paired", "token": raw_token}
