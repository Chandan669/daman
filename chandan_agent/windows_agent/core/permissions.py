from enum import Enum

class PermissionLevel(Enum):
    READ = 1
    EXECUTE = 2
    EXTERNAL_ACTION = 3
    DANGEROUS_ACTION = 4

class PermissionManager:
    def __init__(self):
        self.user_level = PermissionLevel.READ

    def check_permission(self, required_level: PermissionLevel) -> bool:
        return self.user_level.value >= required_level.value

    def elevate(self, level: PermissionLevel):
        self.user_level = level
