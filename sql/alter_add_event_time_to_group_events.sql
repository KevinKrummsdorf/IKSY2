ALTER TABLE group_events
    ADD COLUMN event_time TIME NULL DEFAULT NULL AFTER event_date;
