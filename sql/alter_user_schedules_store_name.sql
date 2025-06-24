ALTER TABLE user_schedules
    ADD COLUMN course_name VARCHAR(255) AFTER user_id;

UPDATE user_schedules us
LEFT JOIN courses c ON us.course_id = c.id
SET us.course_name = COALESCE(us.custom_course_name, c.name);

ALTER TABLE user_schedules
    DROP FOREIGN KEY user_schedules_ibfk_2;
ALTER TABLE user_schedules
    DROP COLUMN course_id,
    DROP COLUMN custom_course_name,
    MODIFY course_name VARCHAR(255) NOT NULL;
