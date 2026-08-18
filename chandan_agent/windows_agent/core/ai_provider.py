from abc import ABC, abstractmethod
import json

class AIProvider(ABC):
    @abstractmethod
    def generate_plan(self, command: str, available_tools: list[dict]) -> list[dict]:
        pass

class DummyAIProvider(AIProvider):
    """
    Fallback provider for testing and early dev.
    """
    def generate_plan(self, command: str, available_tools: list[dict]) -> list[dict]:
        # Simple rule-based mock planner
        if "list files" in command.lower():
            return [{"tool": "filesystem.list", "args": {"path": "."}}]
        elif "youtube" in command.lower():
            return [{"tool": "youtube.upload", "args": {"video_path": "dummy.mp4", "title": "Test"}}]
        elif "stop" in command.lower():
            return [{"tool": "agent.stop", "args": {}}]
        return []

class GeminiAIProvider(AIProvider):
    def __init__(self, api_key: str):
        self.api_key = api_key

    def generate_plan(self, command: str, available_tools: list[dict]) -> list[dict]:
        if not self.api_key:
            raise Exception("Gemini API key not configured")
        # Real implementation would call google.generativeai here
        # Mocking for build spec compliance without real API calls initially
        return [{"tool": "unknown", "args": {"message": "Gemini planner not fully implemented yet."}}]
