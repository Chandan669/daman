# Web Hosting Guide (वेब होस्टिंग गाइड)

Your project is split into two parts for security. Here is exactly how to host it.
(आपका प्रोजेक्ट सुरक्षा के लिए दो भागों में बंटा हुआ है। इसे होस्ट करने का तरीका नीचे दिया गया है।)

---

## 1. Frontend (Website Dashboard) -> `agent.bazrio.com`
This is your React dashboard. You can host this on **Hostinger, cPanel, Netlify, Vercel, or any standard web hosting.**
(यह आपका रिएक्ट डैशबोर्ड है। आप इसे किसी भी नॉर्मल होस्टिंग पर अपलोड कर सकते हैं।)

**Steps:**
1. Open your laptop terminal.
2. Run `./build_frontend.sh` (Mac/Linux) or `build_frontend.bat` (Windows).
3. A new folder will be created: `chandan_agent/frontend/dist`.
4. Upload all the files **inside** the `dist` folder to your web hosting public HTML folder for `agent.bazrio.com`.

---

## 2. Backend Gateway -> `api.agent.bazrio.com` (VPS Required)
Because your agent requires an always-online WebSocket connection to your laptop, the backend **cannot** run on standard shared cPanel hosting. You need a VPS (Virtual Private Server) like DigitalOcean, AWS, or Railway.
(चूंकि आपके एजेंट को आपके लैपटॉप से हमेशा जुड़े रहने के लिए WebSocket की जरूरत है, इसलिए बैकएंड सामान्य cPanel होस्टिंग पर काम नहीं करेगा। आपको VPS की जरूरत होगी।)

**Steps (Using Docker - Recommended):**
1. Upload the entire `chandan_agent/gateway` folder and `docker-compose.yml` to your VPS.
2. SSH into your VPS.
3. Run the following command:
   ```bash
   docker-compose up -d --build
   ```
4. Map your domain to this VPS IP address and set up an SSL certificate (Nginx + Certbot).
5. Open `gateway/.env` on the server and set:
   ```env
   AGENT_SECRET=MakeUpASecurePasswordHere
   ALLOW_DEV_STARTUP=false
   ```

---

## 3. Local Laptop Agent (विंडोज एजेंट)
This runs on your personal computer at home.
(यह आपके घर के पर्सनल कंप्यूटर पर चलेगा।)

1. Ensure Python 3.12+ is installed.
2. Edit `windows_agent/.env` and ensure `VITE_AGENT_WS_URL=wss://api.agent.bazrio.com/ws/agent`.
3. Run `start-agent.bat`.
4. Keep the command prompt open! Your laptop is now listening to your phone.
