-- TRY KUET — PostgreSQL schema
-- Run: psql -d postgres -f scripts/schema.sql

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
  photo_path VARCHAR(500),
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
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  admin_notes TEXT,
  reviewed_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS posts (
  id SERIAL PRIMARY KEY,
  tag VARCHAR(50) NOT NULL,
  title VARCHAR(200) NOT NULL,
  excerpt TEXT NOT NULL,
  content TEXT NOT NULL DEFAULT '',
  image_url VARCHAR(500),
  link_url VARCHAR(500),
  link_label VARCHAR(100) NOT NULL DEFAULT 'Read more →',
  is_published BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS spotlight_items (
  id SERIAL PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  summary TEXT NOT NULL,
  content TEXT NOT NULL DEFAULT '',
  image_url VARCHAR(500),
  link_url VARCHAR(500),
  is_published BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS appeal_requests (
  id SERIAL PRIMARY KEY,
  requester_name VARCHAR(120) NOT NULL,
  requester_phone VARCHAR(20) NOT NULL,
  requester_email VARCHAR(120),
  beneficiary_name VARCHAR(120) NOT NULL,
  case_type VARCHAR(30) NOT NULL,
  target_amount VARCHAR(80),
  location VARCHAR(120),
  description TEXT NOT NULL,
  photo_path VARCHAR(500),
  consent_public BOOLEAN NOT NULL DEFAULT FALSE,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  admin_notes TEXT,
  post_id INT,
  reviewed_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS contact_messages (
  id SERIAL PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS subscribers (
  id SERIAL PRIMARY KEY,
  email VARCHAR(120) NOT NULL UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS site_settings (
  key VARCHAR(100) PRIMARY KEY,
  value TEXT NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
