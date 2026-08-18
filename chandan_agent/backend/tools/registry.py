from typing import Callable, Dict, Any
from core.permissions import PermissionLevel

class Tool:
    def __init__(self, name: str, func: Callable, required_level: PermissionLevel):
        self.name = name
        self.func = func
        self.required_level = required_level

class ToolRegistry:
    def __init__(self):
        self.tools: Dict[str, Tool] = {}

    def register(self, tool: Tool):
        self.tools[tool.name] = tool

    def get_tool(self, name: str) -> Tool:
        return self.tools.get(name)

# Dummy basic tools
import os

def list_files(path: str = "."):
    return os.listdir(path)

registry = ToolRegistry()
registry.register(Tool("filesystem.list", list_files, PermissionLevel.READ))
