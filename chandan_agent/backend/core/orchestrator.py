from tools.registry import ToolRegistry
from core.permissions import PermissionManager, PermissionLevel
from models.database import SessionLocal, TaskRecord, TaskStatus, ApprovalRecord, LogRecord
from core.ai_provider import AIProvider
import json
import threading
import time

class AIOrchestrator:
    def __init__(self, registry: ToolRegistry, permissions: PermissionManager, ai_provider: AIProvider):
        self.registry = registry
        self.permissions = permissions
        self.ai_provider = ai_provider
        self.is_emergency_stop = False
        self.active_processes = []

        # Start background worker
        self.worker_thread = threading.Thread(target=self._process_queue, daemon=True)
        self.worker_thread.start()

    def submit_command(self, natural_command: str) -> dict:
        if self.is_emergency_stop:
            return {"status": "error", "message": "Emergency stop is active."}

        with SessionLocal() as db:
            task = TaskRecord(command=natural_command, status=TaskStatus.QUEUED)
            db.add(task)
            db.commit()
            db.refresh(task)

            db.add(LogRecord(action="TASK_CREATED", details={"task_id": task.id, "command": natural_command}))
            db.commit()

            return {"status": "success", "task_id": task.id, "message": "Task queued successfully."}

    def _process_queue(self):
        while True:
            if self.is_emergency_stop:
                time.sleep(1)
                continue

            with SessionLocal() as db:
                task = db.query(TaskRecord).filter(TaskRecord.status == TaskStatus.QUEUED).first()
                if not task:
                    time.sleep(1)
                    continue

                task.status = TaskStatus.PLANNING
                db.commit()

                self._plan_task(db, task)

    def _plan_task(self, db, task):
        try:
            available_tools = [{"name": t.name, "level": t.required_level.name} for t in self.registry.tools.values()]
            plan = self.ai_provider.generate_plan(task.command, available_tools)

            task.plan = plan

            # Check if any step requires approval
            requires_approval = False
            for step in plan:
                tool = self.registry.get_tool(step.get("tool"))
                if tool and tool.required_level in [PermissionLevel.EXTERNAL_ACTION, PermissionLevel.DANGEROUS_ACTION]:
                    requires_approval = True
                    appr = ApprovalRecord(task_id=task.id, action=step.get("tool"), details=step.get("args"))
                    db.add(appr)

            if requires_approval:
                task.status = TaskStatus.WAITING_APPROVAL
            else:
                task.status = TaskStatus.RUNNING

            db.commit()

            if task.status == TaskStatus.RUNNING:
                self._execute_task(db, task)

        except Exception as e:
            task.status = TaskStatus.FAILED
            task.error = str(e)
            db.commit()

    def _execute_task(self, db, task):
        if self.is_emergency_stop:
            task.status = TaskStatus.CANCELLED
            db.commit()
            return

        try:
            results = []
            for step in task.plan:
                tool_name = step.get("tool")
                args = step.get("args", {})

                tool = self.registry.get_tool(tool_name)
                if not tool:
                    raise Exception(f"Tool not found: {tool_name}")

                if not self.permissions.check_permission(tool.required_level):
                    raise Exception(f"Permission denied for tool: {tool_name}")

                # Execute tool
                res = tool.func(**args)
                results.append({"tool": tool_name, "result": res})

                db.add(LogRecord(action="TOOL_EXECUTED", details={"task_id": task.id, "tool": tool_name, "result": res}))

            task.status = TaskStatus.COMPLETED
            task.result = json.dumps(results)
            db.commit()

        except Exception as e:
            task.status = TaskStatus.FAILED
            task.error = str(e)
            db.commit()

    def approve_task(self, task_id: int, approved: bool):
        with SessionLocal() as db:
            task = db.query(TaskRecord).filter(TaskRecord.id == task_id).first()
            if not task or task.status != TaskStatus.WAITING_APPROVAL:
                return {"status": "error", "message": "Task not found or not waiting for approval"}

            approvals = db.query(ApprovalRecord).filter(ApprovalRecord.task_id == task_id, ApprovalRecord.status == "PENDING").all()
            for app in approvals:
                app.status = "APPROVED" if approved else "REJECTED"

            if approved:
                task.status = TaskStatus.RUNNING
                db.commit()
                # Run async execution
                threading.Thread(target=self._execute_task, args=(db, task)).start()
                return {"status": "success", "message": "Task approved and execution started"}
            else:
                task.status = TaskStatus.CANCELLED
                db.commit()
                return {"status": "success", "message": "Task cancelled"}

    def emergency_stop(self):
        self.is_emergency_stop = True

        # Cancel queued and planning tasks
        with SessionLocal() as db:
            tasks = db.query(TaskRecord).filter(TaskRecord.status.in_([TaskStatus.QUEUED, TaskStatus.PLANNING, TaskStatus.WAITING_APPROVAL])).all()
            for t in tasks:
                t.status = TaskStatus.CANCELLED
            db.commit()

            db.add(LogRecord(action="EMERGENCY_STOP", details={}))
            db.commit()

        return {"status": "stopped", "message": "Emergency Stop Activated! All queued tasks cancelled."}

    def reset_stop(self):
        self.is_emergency_stop = False
        return {"status": "reset", "message": "Emergency stop lifted."}
