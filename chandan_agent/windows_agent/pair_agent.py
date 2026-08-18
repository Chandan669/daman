import requests
import os
from dotenv import load_dotenv

load_dotenv()

API_URL = os.getenv("VITE_AGENT_API_URL", "http://localhost:8000/api")
import platform
DEVICE_ID = os.getenv("DEVICE_ID", f"laptop_{platform.node()}")

def pair():
    print("Welcome to Chandan Agent Pairing")
    print(f"Device ID: {DEVICE_ID}")
    code = input("Enter the 8-character pairing code from the web dashboard: ").strip().upper()

    if not code:
        print("Pairing cancelled.")
        return

    try:
        response = requests.post(f"{API_URL}/auth/pair-agent", params={"device_id": DEVICE_ID, "pairing_code": code})
        if response.status_code == 200:
            data = response.json()
            token = data.get("token")
            print("\nPairing successful!")
            print("Add the following line to your windows_agent/.env file:")
            print(f"AGENT_TOKEN={token}")
            print(f"DEVICE_ID={DEVICE_ID}")
        else:
            print(f"Pairing failed: {response.text}")
    except Exception as e:
        print(f"Error connecting to Gateway: {e}")

if __name__ == "__main__":
    pair()
