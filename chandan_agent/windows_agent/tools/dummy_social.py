from tools.registry import Tool, PermissionLevel

def publish_youtube(video_path: str, title: str):
    return {"status": "published", "platform": "youtube", "video_id": "dummy_123"}

youtube_tool = Tool("youtube.upload", publish_youtube, PermissionLevel.EXTERNAL_ACTION)
