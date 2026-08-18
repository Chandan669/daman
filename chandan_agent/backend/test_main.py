from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def test_read_root():
    response = client.get("/")
    assert response.status_code == 200
    assert response.json() == {"status": "Agent Running"}

def test_run_command():
    response = client.post("/api/command?command=list files")
    assert response.status_code == 200
    assert "result" in response.json()

def test_emergency_stop():
    response = client.post("/api/stop")
    assert response.status_code == 200
    assert response.json()["status"] == "stopped"
