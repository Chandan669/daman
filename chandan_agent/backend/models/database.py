from sqlalchemy import create_engine, Column, Integer, String, Text, DateTime, JSON, Boolean, ForeignKey
from sqlalchemy.orm import declarative_base, sessionmaker, relationship
import datetime

DATABASE_URL = "sqlite:///./chandan_agent.db"

engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False})
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

Base = declarative_base()

class TaskStatus:
    QUEUED = "QUEUED"
    PLANNING = "PLANNING"
    WAITING_APPROVAL = "WAITING_APPROVAL"
    RUNNING = "RUNNING"
    VERIFYING = "VERIFYING"
    COMPLETED = "COMPLETED"
    FAILED = "FAILED"
    CANCELLED = "CANCELLED"

class TaskRecord(Base):
    __tablename__ = "tasks"
    id = Column(Integer, primary_key=True, index=True)
    command = Column(Text, nullable=False)
    status = Column(String, default=TaskStatus.QUEUED)
    plan = Column(JSON, nullable=True) # Structured plan from AI
    result = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.datetime.utcnow, onupdate=datetime.datetime.utcnow)
    error = Column(Text, nullable=True)

class ApprovalRecord(Base):
    __tablename__ = "approvals"
    id = Column(Integer, primary_key=True, index=True)
    task_id = Column(Integer, ForeignKey("tasks.id"))
    action = Column(String, nullable=False)
    details = Column(JSON, nullable=True)
    status = Column(String, default="PENDING") # PENDING, APPROVED, REJECTED
    created_at = Column(DateTime, default=datetime.datetime.utcnow)

    task = relationship("TaskRecord", backref="approvals")

class LogRecord(Base):
    __tablename__ = "audit_logs"
    id = Column(Integer, primary_key=True, index=True)
    timestamp = Column(DateTime, default=datetime.datetime.utcnow)
    action = Column(String, nullable=False)
    details = Column(JSON, nullable=True)

class DevicePairing(Base):
    __tablename__ = "devices"
    id = Column(Integer, primary_key=True, index=True)
    device_id = Column(String, unique=True, index=True)
    token_hash = Column(String)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)

Base.metadata.create_all(bind=engine)
