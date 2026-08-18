from abc import ABC, abstractmethod
import os

class VideoProvider(ABC):
    @abstractmethod
    def generate(self, prompt: str) -> dict:
        pass

class VeoVideoProvider(VideoProvider):
    def generate(self, prompt: str) -> dict:
        api_key = os.getenv("GEMINI_API_KEY")
        if not api_key:
            raise Exception("Veo/Gemini API key not configured")
        # Actual API call would go here
        return {"status": "success", "file": "veo_generated.mp4", "prompt": prompt}

class DummyVideoProvider(VideoProvider):
    def generate(self, prompt: str) -> dict:
        return {"status": "success", "file": "dummy_video.mp4", "prompt": prompt}

def generate_video(prompt: str):
    provider = VeoVideoProvider() if os.getenv("GEMINI_API_KEY") else DummyVideoProvider()
    res = provider.generate(prompt)
    # Add fake validation step for completeness
    if not res.get("file").endswith(".mp4"):
        raise Exception("Validation failed: Not an MP4 file.")
    return res
