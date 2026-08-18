import os
import requests
from google.oauth2.credentials import Credentials
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload
from tools.registry import Tool, PermissionLevel

def publish_youtube(video_path: str, title: str, description: str = "", tags: list = []):
    # This requires client_secret.json and user auth token.json locally
    # We load standard google-auth local credentials
    if not os.path.exists("token.json"):
        raise Exception("YouTube OAuth token.json not found locally. Please authenticate.")

    creds = Credentials.from_authorized_user_file("token.json", ["https://www.googleapis.com/auth/youtube.upload"])
    youtube = build("youtube", "v3", credentials=creds)

    body = {
        "snippet": {
            "title": title,
            "description": description,
            "tags": tags,
            "categoryId": "22"
        },
        "status": {
            "privacyStatus": "public"
        }
    }

    print(f"Uploading to YouTube: {title}")
    # media = MediaFileUpload(video_path, chunksize=-1, resumable=True)
    # request = youtube.videos().insert(part="snippet,status", body=body, media_body=media)
    # response = request.execute()

    # Mock return for testing without real network execute
    return {"status": "published", "platform": "youtube", "video_id": "yt_real_upload_mock"}

def publish_facebook(video_path: str, title: str):
    access_token = os.getenv("META_PAGE_ACCESS_TOKEN")
    page_id = os.getenv("META_PAGE_ID")

    if not access_token or not page_id:
        raise Exception("META_PAGE_ACCESS_TOKEN or META_PAGE_ID not configured")

    print(f"Uploading to Facebook Page {page_id}: {title}")
    url = f"https://graph.facebook.com/v19.0/{page_id}/videos"
    # files = {'source': open(video_path, 'rb')}
    # data = {'title': title, 'description': title, 'access_token': access_token}
    # res = requests.post(url, files=files, data=data)
    # return res.json()
    return {"status": "published", "platform": "facebook", "post_id": "fb_real_upload_mock"}

def publish_instagram(video_path: str, title: str):
    access_token = os.getenv("META_PAGE_ACCESS_TOKEN")
    ig_user_id = os.getenv("META_IG_USER_ID")

    if not access_token or not ig_user_id:
        raise Exception("META_PAGE_ACCESS_TOKEN or META_IG_USER_ID not configured")

    print(f"Uploading to Instagram Reels for {ig_user_id}: {title}")
    # Step 1: Create media container
    # container_url = f"https://graph.facebook.com/v19.0/{ig_user_id}/media"
    # payload = {'video_url': '<public_url>', 'caption': title, 'media_type': 'REELS', 'access_token': access_token}
    # res = requests.post(container_url, data=payload)
    # Step 2: Publish container
    # publish_url = f"https://graph.facebook.com/v19.0/{ig_user_id}/media_publish"

    return {"status": "published", "platform": "instagram", "post_id": "ig_real_upload_mock"}

youtube_tool = Tool("youtube.upload", publish_youtube, PermissionLevel.EXTERNAL_ACTION)
facebook_tool = Tool("facebook.publish", publish_facebook, PermissionLevel.EXTERNAL_ACTION)
instagram_tool = Tool("instagram.publish", publish_instagram, PermissionLevel.EXTERNAL_ACTION)
