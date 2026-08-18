from tools.registry import registry
from tools.safe_fs import fs_list_tool
from tools.safe_cmd import cmd_tool
from tools.browser import browser_tool
from tools.video_provider import generate_video
from tools.social_provider import publish_youtube, publish_facebook
from tools.registry import Tool, PermissionLevel
video_tool = Tool("video.generate", generate_video, PermissionLevel.EXTERNAL_ACTION)
youtube_tool = Tool("youtube.upload", publish_youtube, PermissionLevel.EXTERNAL_ACTION)
facebook_tool = Tool("facebook.publish", publish_facebook, PermissionLevel.EXTERNAL_ACTION)

registry.register(fs_list_tool)
registry.register(cmd_tool)
registry.register(browser_tool)
registry.register(video_tool)
registry.register(youtube_tool)
from tools.project_scanner import scanner_tool
registry.register(scanner_tool)
registry.register(facebook_tool)
