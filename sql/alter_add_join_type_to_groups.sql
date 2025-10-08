ALTER TABLE groups
    ADD COLUMN join_type VARCHAR(10) NOT NULL DEFAULT 'open' CHECK (join_type IN ('open','invite','code')),
    ADD COLUMN invite_code VARCHAR(64);
