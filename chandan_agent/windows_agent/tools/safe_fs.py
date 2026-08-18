import os
from tools.registry import Tool, PermissionLevel

ALLOWED_PATHS = [os.path.abspath(p) for p in [".", "./projects", "/app/chandan_agent/projects"]]

def is_safe_path(path: str) -> bool:
    abs_path = os.path.abspath(path)
    for allowed in ALLOWED_PATHS:
        if abs_path.startswith(allowed):
            return True
    return False

def list_files(path: str = "."):
    if not is_safe_path(path):
        raise ValueError("Path is outside allowed directories.")
    return os.listdir(path)

fs_list_tool = Tool("filesystem.list", list_files, PermissionLevel.READ)
