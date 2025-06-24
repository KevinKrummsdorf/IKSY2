ALTER TABLE user_schedules
    ADD COLUMN custom_course_name VARCHAR(255) AFTER course_id,
    MODIFY course_id INT NULL;
