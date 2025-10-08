ALTER TABLE user_schedules
    ADD COLUMN course_name VARCHAR(255);

UPDATE user_schedules us
SET course_name = COALESCE(us.custom_course_name, c.name)
FROM courses c
WHERE c.id = us.course_id;

UPDATE user_schedules
SET course_name = custom_course_name
WHERE course_id IS NULL;

ALTER TABLE user_schedules
    DROP CONSTRAINT user_schedules_course_id_fkey;
ALTER TABLE user_schedules
    DROP COLUMN course_id,
    DROP COLUMN custom_course_name,
    ALTER COLUMN course_name SET NOT NULL;
