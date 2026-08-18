from tools.registry import ToolRegistry
from core.permissions import PermissionManager, PermissionLevel

class AIOrchestrator:
    def __init__(self, registry: ToolRegistry, permissions: PermissionManager):
        self.registry = registry
        self.permissions = permissions
        self.is_emergency_stop = False

    def execute_command(self, natural_command: str):
        if self.is_emergency_stop:
            return {"status": "error", "message": "Emergency stop is active."}

        # Very basic dummy planner
        if "list files" in natural_command.lower():
            tool = self.registry.get_tool("filesystem.list")
            if tool and self.permissions.check_permission(tool.required_level):
                return {"status": "success", "result": tool.func(".")}
            else:
                return {"status": "error", "message": "Permission denied or tool not found."}

        return {"status": "queued", "plan": "Planning pending for: " + natural_command}

    def emergency_stop(self):
        self.is_emergency_stop = True
        return {"status": "stopped", "message": "Emergency Stop Activated!"}
