CREATE TABLE group_invites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    invited_user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    used_at DATETIME NULL DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    CONSTRAINT fk_inv_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_user FOREIGN KEY (invited_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_inv_group_user (group_id, invited_user_id)
);
