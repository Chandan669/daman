from fastapi.testclient import TestClient
from main import app
from models.database import SessionLocal, DevicePairing, Base, engine
import pytest
import os
import secrets

client = TestClient(app)

@pytest.fixture(autouse=True)
def run_around_tests():
    Base.metadata.drop_all(bind=engine)
    Base.metadata.create_all(bind=engine)
    yield
    Base.metadata.drop_all(bind=engine)

def test_generate_pairing_code():
    response = client.post("/api/auth/generate-pairing")
    assert response.status_code == 200
    assert "pairing_code" in response.json()

def test_device_pairing():
    # Simulate device confirming pair
    response = client.post("/api/auth/pair-agent?device_id=test_dev&pairing_code=ABCD")
    assert response.status_code == 200
    assert "token" in response.json()

def test_unauthorized_access():
    response = client.get("/api/tasks")
    assert response.status_code in [401, 403]

def test_authorized_access():
    # Setup token
    raw_token = secrets.token_hex(32)
    from api.auth import hash_token
    db = SessionLocal()
    db.add(DevicePairing(device_id="test", token_hash=hash_token(raw_token), is_active=True))
    db.commit()
    db.close()

    response = client.get("/api/tasks", headers={"Authorization": f"Bearer {raw_token}"})
    assert response.status_code == 200
