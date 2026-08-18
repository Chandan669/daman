from fastapi import APIRouter, WebSocket, WebSocketDisconnect
from typing import Dict
import json
import datetime
from models.database import SessionLocal, DevicePairing

router = APIRouter()

class ConnectionManager:
    def __init__(self):
        self.active_connections: Dict[str, WebSocket] = {}

    async def connect(self, device_id: str, websocket: WebSocket):
        await websocket.accept()
        self.active_connections[device_id] = websocket

    def disconnect(self, device_id: str):
        if device_id in self.active_connections:
            del self.active_connections[device_id]

    async def send_personal_message(self, message: dict, device_id: str):
        if device_id in self.active_connections:
            await self.active_connections[device_id].send_json(message)

manager = ConnectionManager()

@router.websocket("/agent/{device_id}")
async def websocket_endpoint(websocket: WebSocket, device_id: str, token: str):
    # Authenticate WS connection
    from api.auth import hash_token
    db = SessionLocal()
    hashed = hash_token(token)
    device = db.query(DevicePairing).filter(DevicePairing.device_id == device_id, DevicePairing.token_hash == hashed, DevicePairing.is_active == True).first()

    if not device:
        await websocket.close(code=1008)
        db.close()
        return

    await manager.connect(device_id, websocket)
    try:
        while True:
            data = await websocket.receive_json()
            if data.get("type") == "heartbeat":
                device.last_heartbeat = datetime.datetime.utcnow()
                device.agent_version = data.get("version")
                device.os_info = data.get("os")
                device.cpu_usage = data.get("cpu")
                device.ram_usage = data.get("ram")
                db.commit()
                await manager.send_personal_message({"type": "heartbeat_ack"}, device_id)
            # Handle task status updates from agent here
    except WebSocketDisconnect:
        manager.disconnect(device_id)
    finally:
        db.close()
