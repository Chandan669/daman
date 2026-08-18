import json
import threading
import time
from core.permissions import PermissionLevel

# Stub Task Statuses locally since DB is on Gateway
class TaskStatus:
    QUEUED = "QUEUED"
    PLANNING = "PLANNING"
    WAITING_APPROVAL = "WAITING_APPROVAL"
    RUNNING = "RUNNING"
    COMPLETED = "COMPLETED"
    FAILED = "FAILED"
    CANCELLED = "CANCELLED"

class DummyTask:
    def __init__(self, task_id, command):
        self.id = task_id
        self.command = command
        self.status = TaskStatus.QUEUED
        self.plan = []

class AIOrchestrator:
    def __init__(self, registry, permissions, ai_provider):
        self.registry = registry
        self.permissions = permissions
        self.ai_provider = ai_provider
        self.is_emergency_stop = False
        self.tasks = {}

    def submit_command(self, natural_command: str, task_id: int):
        if self.is_emergency_stop:
            return

        task = DummyTask(task_id, natural_command)
        self.tasks[task_id] = task
        # Normally sync status to Gateway here
        self._plan_task(task)

    def _plan_task(self, task):
        try:
            available_tools = [{"name": t.name, "level": t.required_level.name} for t in self.registry.tools.values()]
            plan = self.ai_provider.generate_plan(task.command, available_tools)
            task.plan = plan

            requires_approval = False
            for step in plan:
                tool = self.registry.get_tool(step.get("tool"))
                if tool and tool.required_level in [PermissionLevel.EXTERNAL_ACTION, PermissionLevel.DANGEROUS_ACTION]:
                    requires_approval = True

            if requires_approval:
                task.status = TaskStatus.WAITING_APPROVAL
                print(f"Task {task.id} waiting for approval from Gateway...")
            else:
                task.status = TaskStatus.RUNNING
                threading.Thread(target=self._execute_task, args=(task,)).start()
        except Exception as e:
            task.status = TaskStatus.FAILED
            print(f"Task {task.id} failed planning: {e}")

    def _execute_task(self, task):
        if self.is_emergency_stop:
            task.status = TaskStatus.CANCELLED
            return

        try:
            print(f"Executing Task {task.id}...")
            for step in task.plan:
                tool_name = step.get("tool")
                args = step.get("args", {})
                tool = self.registry.get_tool(tool_name)

                if tool:
                    print(f"Running {tool_name} with args {args}")
                    tool.func(**args)

            task.status = TaskStatus.COMPLETED
            print(f"Task {task.id} completed successfully.")
            # Sync to Gateway...
        except Exception as e:
            task.status = TaskStatus.FAILED
            print(f"Task {task.id} failed: {e}")

    def approve_task(self, task_id: int, approved: bool):
        task = self.tasks.get(task_id)
        if not task: return

        if approved:
            task.status = TaskStatus.RUNNING
            threading.Thread(target=self._execute_task, args=(task,)).start()
        else:
            task.status = TaskStatus.CANCELLED
            print(f"Task {task.id} cancelled by Gateway.")

    def emergency_stop(self):
        self.is_emergency_stop = True
        for t in self.tasks.values():
            if t.status in [TaskStatus.QUEUED, TaskStatus.PLANNING, TaskStatus.WAITING_APPROVAL]:
                t.status = TaskStatus.CANCELLED
