-- Tabelle: courses (Kurse)
CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    professor VARCHAR(255)
);

-- Tabelle: weekdays (Wochentage)
CREATE TABLE weekdays (
    id SMALLINT PRIMARY KEY,
    day_name VARCHAR(20) NOT NULL UNIQUE
);

-- Tabelle: time_slots (Zeitfenster)
CREATE TABLE time_slots (
    id SERIAL PRIMARY KEY,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    UNIQUE (start_time, end_time)
);

-- Tabelle: user_schedules (Zuweisung von Kursen zu Benutzern)
CREATE TABLE user_schedules (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_name VARCHAR(255) NOT NULL,
    weekday_id SMALLINT NOT NULL REFERENCES weekdays(id),
    time_slot_id INT NOT NULL REFERENCES time_slots(id),
    room VARCHAR(50),
    notes TEXT,
    UNIQUE (user_id, weekday_id, time_slot_id)
);

-- Optional: Beispielhafte Befüllung der Tabelle weekdays
INSERT INTO weekdays (id, day_name) VALUES
(1, 'Montag'),
(2, 'Dienstag'),
(3, 'Mittwoch'),
(4, 'Donnerstag'),
(5, 'Freitag'),
(6, 'Samstag'),
(7, 'Sonntag');
