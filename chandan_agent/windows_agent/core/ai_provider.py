from abc import ABC, abstractmethod
import json
import os
import google.generativeai as genai

class AIProvider(ABC):
    @abstractmethod
    def generate_plan(self, command: str, available_tools: list[dict]) -> list[dict]:
        pass

class DummyAIProvider(AIProvider):
    def generate_plan(self, command: str, available_tools: list[dict]) -> list[dict]:
        if "youtube" in command.lower() or "facebook" in command.lower() or "instagram" in command.lower() or "video" in command.lower():
            # Dummy fallback if no API key
            return [
                {"tool": "video.generate", "args": {"prompt": "Automated video topic"}},
                {"tool": "video.edit", "args": {"video_path": "veo_generated.mp4", "preset": "SHORT_VERTICAL"}},
                {"tool": "youtube.upload", "args": {"video_path": "edited.mp4", "title": "Auto Generated Short"}},
                {"tool": "facebook.publish", "args": {"video_path": "edited.mp4", "title": "Auto Generated Reel"}},
                {"tool": "instagram.publish", "args": {"video_path": "edited.mp4", "title": "Auto Generated Reel"}}
            ]
        return []

class GeminiAIProvider(AIProvider):
    def __init__(self):
        self.api_key = os.getenv("GEMINI_API_KEY")
        if self.api_key:
            genai.configure(api_key=self.api_key)
            self.model = genai.GenerativeModel('gemini-1.5-pro-latest')

    def generate_plan(self, command: str, available_tools: list[dict]) -> list[dict]:
        if not self.api_key:
            print("Gemini API key not found, falling back to basic planning.")
            return DummyAIProvider().generate_plan(command, available_tools)

        tools_str = json.dumps(available_tools, indent=2)
        prompt = f"""
You are the Chandan Agent Orchestrator. Convert the user command into a strictly ordered JSON array of tool execution steps.
Only output the JSON array. Do not output markdown code blocks.

Available Tools:
{tools_str}

User Command: {command}

Output Format Example:
[
  {{"tool": "video.generate", "args": {{"prompt": "A scenic sunset over mountains"}}}},
  {{"tool": "video.edit", "args": {{"video_path": "veo_generated.mp4", "preset": "SHORT_VERTICAL"}}}},
  {{"tool": "youtube.upload", "args": {{"video_path": "edited.mp4", "title": "Mountain Sunset"}}}}
]
"""
        try:
            response = self.model.generate_content(prompt)
            # Clean up markdown if model still included it
            text = response.text.replace("```json", "").replace("```", "").strip()
            return json.loads(text)
        except Exception as e:
            print(f"Gemini planning failed: {e}")
            return DummyAIProvider().generate_plan(command, available_tools)

def get_ai_provider():
    if os.getenv("GEMINI_API_KEY"):
        return GeminiAIProvider()
    return DummyAIProvider()
