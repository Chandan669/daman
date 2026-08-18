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

registry = ToolRegistry()
