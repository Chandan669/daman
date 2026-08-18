from tools.registry import registry
from tools.safe_fs import fs_list_tool
from tools.safe_cmd import cmd_tool
from tools.browser import browser_tool
from tools.video_provider import video_tool, edit_tool
from tools.social_provider import youtube_tool, facebook_tool, instagram_tool
from tools.registry import Tool, PermissionLevel




registry.register(fs_list_tool)
registry.register(cmd_tool)
registry.register(browser_tool)
registry.register(video_tool)
registry.register(youtube_tool)
from tools.project_scanner import scanner_tool
registry.register(scanner_tool)
registry.register(facebook_tool)
registry.register(edit_tool)
registry.register(instagram_tool)
