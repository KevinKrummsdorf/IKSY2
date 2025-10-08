ALTER TABLE todos
    ADD COLUMN priority VARCHAR(6) NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high'));

