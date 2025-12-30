-- 1. Tabelle 'users' erstellen
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP DEFAULT NULL
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

-- Tabelle: roles
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (role_name) VALUES ('admin'), ('moderator'), ('user');

-- Tabelle: user_roles
CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Tabelle: user_verification
CREATE TABLE user_verification (
    user_id INT PRIMARY KEY,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: verification_tokens
CREATE TABLE verification_tokens (
    user_id INT PRIMARY KEY,
    verification_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: user_security
CREATE TABLE user_security (
    user_id INT PRIMARY KEY,
    failed_attempts INT NOT NULL DEFAULT 0,
    account_locked BOOLEAN NOT NULL DEFAULT FALSE,
    lock_until TIMESTAMP DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: user_2fa
CREATE TABLE user_2fa (
    user_id INT PRIMARY KEY,
    twofa_secret VARCHAR(255) DEFAULT NULL,
    is_twofa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: password_reset_tokens
CREATE TABLE password_reset_tokens (
    user_id INT PRIMARY KEY,
    reset_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: profile
CREATE TABLE profile (
    user_id INT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    birthdate DATE,
    profile_picture VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: social_media
CREATE TABLE social_media (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    username VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (user_id, platform)
);

-- Tabelle: groups
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    join_type VARCHAR(20) NOT NULL DEFAULT 'open' CHECK (join_type IN ('open', 'invite_only', 'closed')),
    invite_code VARCHAR(255) UNIQUE,
    group_picture VARCHAR(255)
);

-- Tabelle: group_members
CREATE TABLE group_members (
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: group_roles
CREATE TABLE group_roles (
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'member' CHECK (role IN ('admin', 'moderator', 'member')),
    PRIMARY KEY (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: group_invites
CREATE TABLE group_invites (
    id SERIAL PRIMARY KEY,
    group_id INT NOT NULL,
    invited_user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: group_events
CREATE TABLE group_events (
    id SERIAL PRIMARY KEY,
    group_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    repeat_interval VARCHAR(20) DEFAULT 'none' CHECK (repeat_interval IN ('none', 'weekly', 'biweekly', 'monthly')),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- Tabelle: todos
CREATE TABLE todos (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    text TEXT NOT NULL,
    due_date DATE,
    priority VARCHAR(20) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
    is_done BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: courses
CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    professor VARCHAR(255)
);

-- Tabelle: materials
CREATE TABLE materials (
    id SERIAL PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Tabelle: uploads
CREATE TABLE uploads (
    id SERIAL PRIMARY KEY,
    stored_name VARCHAR(255) NOT NULL,
    material_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved BOOLEAN NOT NULL DEFAULT FALSE,
    is_rejected BOOLEAN NOT NULL DEFAULT FALSE,
    group_id INT,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
);

-- Tabelle: material_ratings
CREATE TABLE material_ratings (
    material_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    PRIMARY KEY (material_id, user_id),
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: captcha_log
CREATE TABLE captcha_log (
    id SERIAL PRIMARY KEY,
    token TEXT,
    success BOOLEAN,
    score FLOAT,
    action VARCHAR(255),
    hostname VARCHAR(255),
    error_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabelle: contact_requests
CREATE TABLE contact_requests (
    contact_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabelle: pending_course_suggestions
CREATE TABLE pending_course_suggestions (
    id SERIAL PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    suggested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved BOOLEAN,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabelle: subjects
CREATE TABLE subjects (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- Tabelle: rooms
CREATE TABLE rooms (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- Tabelle: timetable
CREATE TABLE timetable (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    weekday VARCHAR(20) NOT NULL,
    time VARCHAR(20) NOT NULL,
    subject_id INT,
    room_id INT,
    slot_index INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

-- Tabelle: weekdays
CREATE TABLE weekdays (
    id SMALLINT PRIMARY KEY,
    day_name VARCHAR(20) NOT NULL UNIQUE
);

-- Tabelle: time_slots
CREATE TABLE time_slots (
    id SERIAL PRIMARY KEY,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    UNIQUE (start_time, end_time)
);

-- Tabelle: user_schedules
CREATE TABLE user_schedules (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    weekday_id SMALLINT NOT NULL,
    time_slot_id INT NOT NULL,
    room VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (weekday_id) REFERENCES weekdays(id),
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id),
    UNIQUE (user_id, weekday_id, time_slot_id)
);

-- Beispielhafte Befüllung der Tabelle weekdays
INSERT INTO weekdays (id, day_name) VALUES
(1, 'Montag'),
(2, 'Dienstag'),
(3, 'Mittwoch'),
(4, 'Donnerstag'),
(5, 'Freitag'),
(6, 'Samstag'),
(7, 'Sonntag');
