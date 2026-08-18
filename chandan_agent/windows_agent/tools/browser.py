from playwright.sync_api import sync_playwright
from tools.registry import Tool, PermissionLevel

ALLOWED_DOMAINS = ["github.com", "example.com"]

def safe_screenshot(url: str, output: str = "screenshot.png"):
    is_safe = any(domain in url for domain in ALLOWED_DOMAINS)
    if not is_safe:
        raise ValueError("Domain is not in the allowlist.")

    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.goto(url)
        page.screenshot(path=output)
        browser.close()
    return {"status": "success", "file": output}

browser_tool = Tool("browser.screenshot", safe_screenshot, PermissionLevel.READ)
