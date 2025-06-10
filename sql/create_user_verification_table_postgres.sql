CREATE TABLE user_verification (
    user_id INT PRIMARY KEY,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
