import asyncio
import websockets
import json
import os
import psutil
import platform
from dotenv import load_dotenv

load_dotenv()

AGENT_VERSION = "1.0.0"
GATEWAY_WS_URL = os.getenv("VITE_AGENT_WS_URL", "ws://localhost:8000/ws/agent")
DEVICE_ID = os.getenv("DEVICE_ID", f"laptop_{platform.node()}")
TOKEN = os.getenv("AGENT_TOKEN") # Will be saved after pairing

from core.orchestrator import AIOrchestrator
from tools.registry import registry
from core.permissions import PermissionManager
from core.ai_provider import get_ai_provider

pm = PermissionManager()
ai_provider = get_ai_provider()
orchestrator = AIOrchestrator(registry, pm, ai_provider)

async def heartbeat(websocket):
    while True:
        try:
            await websocket.send(json.dumps({
                "type": "heartbeat",
                "version": AGENT_VERSION,
                "os": platform.system(),
                "cpu": psutil.cpu_percent(),
                "ram": psutil.virtual_memory().percent
            }))
            await asyncio.sleep(10)
        except Exception:
            break

async def listen_to_gateway():
    if not TOKEN:
        print("ERROR: AGENT_TOKEN not set. Run pairing script first.")
        return

    url = f"{GATEWAY_WS_URL}/{DEVICE_ID}?token={TOKEN}"
    while True:
        try:
            print(f"Connecting to Gateway at {GATEWAY_WS_URL}...")
            async with websockets.connect(url) as websocket:
                print("Connected securely to Gateway.")
                asyncio.create_task(heartbeat(websocket))

                async for message in websocket:
                    data = json.loads(message)
                    if data.get("type") == "new_task":
                        print(f"Received Task: {data['command']}")
                        # Forward to local orchestrator
                        orchestrator.submit_command(data['command'], task_id=data['task_id'])

                    elif data.get("type") == "emergency_stop":
                        print("EMERGENCY STOP RECEIVED from Gateway")
                        orchestrator.emergency_stop()

                    elif data.get("type") == "approval_resolved":
                        orchestrator.approve_task(data['task_id'], data['approved'])
        except Exception as e:
            print(f"Connection lost. Retrying in 5 seconds... ({e})")
            await asyncio.sleep(5)

if __name__ == "__main__":
    try:
        asyncio.run(listen_to_gateway())
    except KeyboardInterrupt:
        print("Agent shutting down.")
