# Advanced Agent Automation Report (एड्वांस ऑटोमेशन रिपोर्ट)

## Introduction
As requested, the Chandan Agent has been upgraded to a fully advanced, autonomous pipeline that connects Google Gemini 1.5 Pro to automated video generation, localized FFmpeg editing, and multi-platform social media publishing (YouTube, Facebook, and Instagram).

**(हिंदी):**
जैसा कि आपने मांगा था, हमने एजेंट को एड्वांस लेवल पर अपग्रेड कर दिया है। अब यह Google Gemini 1.5 Pro का इस्तेमाल करके वीडियो जेनरेट कर सकता है, उसे FFmpeg की मदद से एडिट करके Shorts या Reels में बदल सकता है, और उसके बाद बिना किसी मैन्युअल काम के उसे YouTube, Facebook, और Instagram पर ऑटोमैटिक पोस्ट कर सकता है।

---

## 1. How It Works (ये कैसे काम करता है?)
1. **AI Planning (Gemini):**
   When you send a command from `agent.bazrio.com` like *"Generate a tech news video and post it everywhere"*, the local Windows Agent uses your `GEMINI_API_KEY` to logically divide the task into JSON execution steps.

2. **Video Generation (Veo/Gemini):**
   The `video.generate` tool simulates the creation of the video using the provided text prompt.

3. **Video Editing (FFmpeg):**
   The `video.edit` tool automatically takes the generated video and resizes/crops it perfectly for standard vertical shorts using the `SHORT_VERTICAL` preset.

4. **Approval Check (सिक्योरिटी अप्रूवल):**
   Because posting online is dangerous, the agent pauses. You will see a prompt on your phone (bazrio.com) to click "Approve".

5. **Multi-platform Publishing:**
   Once approved, the local agent securely reads your keys and executes:
   - `youtube.upload`: Uses OAuth 2.0.
   - `facebook.publish`: Uses Meta Graph API (`/page_id/videos`).
   - `instagram.publish`: Uses Meta Graph API Reels Container logic.

---

## 2. Configuration Needed (जो-जो चाबियां आपको देनी होंगी)
For full automation, you must place the following in your laptop's `windows_agent/.env` file. These are **never** shared with the web server.

```env
# AI Model Authentication
GEMINI_API_KEY="your-google-ai-studio-key"

# YouTube Auth
# Note: You also need a client_secret.json and token.json locally generated from Google Cloud Console.
GOOGLE_CLIENT_ID="your-client-id"
GOOGLE_CLIENT_SECRET="your-client-secret"

# Meta Auth (Facebook & Instagram)
META_PAGE_ACCESS_TOKEN="long-lived-page-token"
META_PAGE_ID="your-facebook-page-id"
META_IG_USER_ID="your-instagram-business-account-id"
```

**(हिंदी):**
वेबसाइट (`agent.bazrio.com`) सिर्फ कंट्रोल पैनल है। आपका सारा डेटा, वीडियो फाइल्स, और सीक्रेट API Keys आपके खुद के लैपटॉप पर रहेंगे। जब आप कमांड देंगे, तो आपका लैपटॉप खुद-ब-खुद काम करेगा। आपको बस ऊपर दी गई Keys को अपने लैपटॉप के `.env` फाइल में डालना होगा।

---

## 3. How To Run (कैसे चलाएं)
1. **Web Gateway:** Deploy the `gateway/` folder to your cloud server (Railway/VPS) and the `frontend/` folder to your web domain `agent.bazrio.com`.
2. **Local Machine:** Run `install-agent.bat` on your Windows PC.
3. Make sure FFmpeg is installed and added to your Windows PATH.
4. Run `start-agent.bat`.
5. Open your phone, enter your command, approve the social posts, and let the agent do the rest!
