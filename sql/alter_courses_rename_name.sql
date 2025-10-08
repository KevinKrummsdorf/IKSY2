ALTER TABLE courses
    RENAME COLUMN course_name TO name;
ALTER TABLE courses
    ALTER COLUMN name TYPE VARCHAR(255);
ALTER TABLE courses
    ALTER COLUMN name SET NOT NULL;
ALTER TABLE courses
    ADD CONSTRAINT courses_name_unique UNIQUE (name);

