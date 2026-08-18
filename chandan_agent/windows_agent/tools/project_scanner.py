import os
import json
from tools.registry import Tool, PermissionLevel

def scan_project(path: str):
    from tools.safe_fs import is_safe_path
    if not is_safe_path(path):
        raise ValueError("Path is outside allowed directories.")

    report = {
        "frameworks": [],
        "dependencies": "unknown",
        "has_git": os.path.isdir(os.path.join(path, ".git")),
        "obvious_errors": []
    }

    if os.path.exists(os.path.join(path, "package.json")):
        report["frameworks"].append("Node.js")
        with open(os.path.join(path, "package.json")) as f:
            try:
                data = json.load(f)
                deps = data.get("dependencies", {})
                if "react" in deps: report["frameworks"].append("React")
                if "vite" in data.get("devDependencies", {}): report["frameworks"].append("Vite")
                report["dependencies"] = "npm"
            except Exception as e:
                report["obvious_errors"].append("Invalid package.json")

    if os.path.exists(os.path.join(path, "composer.json")):
        report["frameworks"].append("PHP")
        with open(os.path.join(path, "composer.json")) as f:
            try:
                data = json.load(f)
                if "laravel/framework" in data.get("require", {}):
                    report["frameworks"].append("Laravel")
                report["dependencies"] = "composer"
            except:
                report["obvious_errors"].append("Invalid composer.json")

    if os.path.exists(os.path.join(path, "requirements.txt")):
        report["frameworks"].append("Python")
        report["dependencies"] = "pip"

    return report

scanner_tool = Tool("project.scan", scan_project, PermissionLevel.READ)
