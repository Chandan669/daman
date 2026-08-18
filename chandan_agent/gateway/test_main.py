from fastapi.testclient import TestClient
from main import app
from models.database import SessionLocal, DevicePairing, Base, engine
import os
import pytest

client = TestClient(app)

@pytest.fixture(autouse=True)
def run_around_tests():
    Base.metadata.drop_all(bind=engine)
    Base.metadata.create_all(bind=engine)
    yield
    Base.metadata.drop_all(bind=engine)

def test_pair_device():
    os.environ["AGENT_SECRET"] = "dev-mode"
    response = client.post("/api/auth/pair?device_id=test_dev")
    assert response.status_code == 200
    assert "token" in response.json()

def test_run_command_unauthorized():
    os.environ["AGENT_SECRET"] = "prod"
    response = client.post("/api/command?command=test")
    assert response.status_code == 401 # FastAPI HTTPBearer without credentials raises 401

def test_run_command_authorized():
    os.environ["AGENT_SECRET"] = "dev-mode"
    # dev-mode bypasses token check in verify_token
    response = client.post("/api/command?command=test")
    assert response.status_code == 200
    assert "task_id" in response.json()

def test_emergency_stop():
    os.environ["AGENT_SECRET"] = "dev-mode"
    response = client.post("/api/stop")
    assert response.status_code == 200
    assert response.json()["status"] == "stopped"
