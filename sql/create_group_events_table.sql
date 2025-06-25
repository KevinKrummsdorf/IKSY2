CREATE TABLE group_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    repeat_interval VARCHAR(20) NOT NULL DEFAULT 'none',
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    INDEX idx_group_date (group_id, event_date)
);

