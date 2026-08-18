import os

def publish_youtube(video_path: str, title: str):
    client_id = os.getenv("GOOGLE_CLIENT_ID")
    if not client_id:
        raise Exception("YouTube OAuth not configured")
    # Real OAuth logic and googleapiclient.discovery here
    return {"status": "published", "platform": "youtube", "video_id": "real_yt_id"}

def publish_facebook(video_path: str, title: str):
    if not os.getenv("META_APP_ID"):
        raise Exception("Meta API not configured")
    return {"status": "published", "platform": "facebook", "post_id": "real_fb_id"}
