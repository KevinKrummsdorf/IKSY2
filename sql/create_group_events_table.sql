CREATE TABLE group_events (
    id SERIAL PRIMARY KEY,
    group_id INT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    repeat_interval VARCHAR(20) NOT NULL DEFAULT 'none'
);

CREATE INDEX idx_group_date ON group_events (group_id, event_date);

