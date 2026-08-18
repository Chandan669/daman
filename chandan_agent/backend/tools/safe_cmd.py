import subprocess
from tools.registry import Tool, PermissionLevel

# Strict allowlist of commands
ALLOWED_CMDS = ["npm test", "npm run build", "python -m pytest", "php artisan test"]

def run_command(cmd: str):
    is_allowed = False
    for allowed in ALLOWED_CMDS:
        if cmd.startswith(allowed):
            is_allowed = True
            break

    if not is_allowed:
        raise ValueError("Command is not in the allowlist.")

    result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return {"stdout": result.stdout, "stderr": result.stderr, "code": result.returncode}

cmd_tool = Tool("terminal.run", run_command, PermissionLevel.EXECUTE)
