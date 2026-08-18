from fastapi import FastAPI, WebSocket, WebSocketDisconnect
from api.routes import router
from fastapi.middleware.cors import CORSMiddleware
import json

app = FastAPI(title="Chandan Agent API")
app.include_router(router, prefix="/api")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class ConnectionManager:
    def __init__(self):
        self.active_connections: list[WebSocket] = []

    async def connect(self, websocket: WebSocket):
        await websocket.accept()
        self.active_connections.append(websocket)

    def disconnect(self, websocket: WebSocket):
        self.active_connections.remove(websocket)

    async def broadcast(self, message: str):
        for connection in self.active_connections:
            await connection.send_text(message)

manager = ConnectionManager()

@app.get("/")
def read_root():
    return {"status": "Agent Running"}

@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    await manager.connect(websocket)
    try:
        while True:
            data = await websocket.receive_text()
            # Simple echo for now
            response = {"event": "status", "data": f"Received command: {data}"}
            await manager.broadcast(json.dumps(response))
    except WebSocketDisconnect:
        manager.disconnect(websocket)
