from fastapi import APIRouter
from core.orchestrator import AIOrchestrator
from tools.registry import registry
from core.permissions import PermissionManager

router = APIRouter()
pm = PermissionManager()
orchestrator = AIOrchestrator(registry, pm)

@router.post("/command")
def run_command(command: str):
    return orchestrator.execute_command(command)

@router.post("/stop")
def stop_agent():
    return orchestrator.emergency_stop()
