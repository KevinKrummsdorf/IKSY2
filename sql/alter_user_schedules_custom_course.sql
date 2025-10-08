ALTER TABLE user_schedules
    ADD COLUMN custom_course_name VARCHAR(255);
ALTER TABLE user_schedules
    ALTER COLUMN course_id DROP NOT NULL;
