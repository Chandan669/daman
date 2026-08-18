from sqlalchemy import create_engine, Column, Integer, String, Text, DateTime, JSON
from sqlalchemy.orm import declarative_base, sessionmaker
import datetime

DATABASE_URL = "sqlite:///./chandan_agent.db"

engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False})
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

Base = declarative_base()

class TaskRecord(Base):
    __tablename__ = "tasks"
    id = Column(Integer, primary_key=True, index=True)
    command = Column(Text, nullable=False)
    status = Column(String, default="QUEUED")
    result = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)

class LogRecord(Base):
    __tablename__ = "audit_logs"
    id = Column(Integer, primary_key=True, index=True)
    timestamp = Column(DateTime, default=datetime.datetime.utcnow)
    action = Column(String, nullable=False)
    details = Column(JSON, nullable=True)

Base.metadata.create_all(bind=engine)
