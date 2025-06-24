-- Tabelle: courses (Kurse)
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    professor VARCHAR(255)
);

-- Tabelle: weekdays (Wochentage)
CREATE TABLE weekdays (
    id TINYINT PRIMARY KEY,
    day_name VARCHAR(20) NOT NULL UNIQUE
);

-- Tabelle: time_slots (Zeitfenster)
CREATE TABLE time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    UNIQUE (start_time, end_time)
);

-- Tabelle: user_schedules (Zuweisung von Kursen zu Benutzern)
CREATE TABLE user_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NULL,
    custom_course_name VARCHAR(255),
    weekday_id TINYINT NOT NULL,
    time_slot_id INT NOT NULL,
    room VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (weekday_id) REFERENCES weekdays(id),
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id),
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
