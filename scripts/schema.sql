-- TRY KUET — PostgreSQL schema
-- Run: psql -U postgres -f scripts/schema.sql

CREATE DATABASE try_kuet
  ENCODING 'UTF8'
  TEMPLATE template0;

\c try_kuet

CREATE TABLE IF NOT EXISTS join_applications (
  id SERIAL PRIMARY KEY,
  fullname VARCHAR(120) NOT NULL,
  roll VARCHAR(20) NOT NULL UNIQUE,
  department VARCHAR(80) NOT NULL,
  batch VARCHAR(20) NOT NULL,
  semester VARCHAR(10),
  blood_group VARCHAR(10),
  email VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  facebook VARCHAR(300),
  hall VARCHAR(120),
  why_join TEXT NOT NULL,
  experience TEXT,
  skills JSONB,
  other_skills VARCHAR(300),
  weekly_hours VARCHAR(20) NOT NULL,
  meetings VARCHAR(20) NOT NULL,
  emergency_name VARCHAR(120),
  emergency_phone VARCHAR(20),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
