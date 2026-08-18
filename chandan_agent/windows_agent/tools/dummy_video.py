from tools.registry import Tool, PermissionLevel

class DummyVideoProvider:
    def generate(self, prompt: str):
        return {"status": "success", "file": "dummy_video.mp4", "prompt": prompt}

def generate_video(prompt: str):
    provider = DummyVideoProvider()
    return provider.generate(prompt)

video_tool = Tool("video.generate", generate_video, PermissionLevel.EXTERNAL_ACTION)
