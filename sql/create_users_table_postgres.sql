-- 1. Tabelle 'users' erstellen
CREATE TABLE users (
    id SERIAL PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP DEFAULT NULL,

    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin', 'moderator')),

    failed_attempts INT NOT NULL DEFAULT 0,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expiry TIMESTAMP DEFAULT NULL,

    account_locked BOOLEAN NOT NULL DEFAULT FALSE,
    lock_until TIMESTAMP DEFAULT NULL,

    two_factor_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    two_factor_secret VARCHAR(255) DEFAULT NULL
);

-- 2. Trigger-Funktion für updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = CURRENT_TIMESTAMP;
   RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 3. Trigger einrichten
CREATE TRIGGER set_updated_at
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();