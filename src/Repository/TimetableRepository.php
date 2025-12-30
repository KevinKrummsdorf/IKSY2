<?php

declare(strict_types=1);

class TimetableRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function deleteAllTimetableEntries(int $userId): void
    {
        $this->db->execute('DELETE FROM timetable WHERE user_id = ?', [$userId]);
    }

    public function insertTimetableEntry(int $userId, string $weekday, string $time, string $subject, string $room, int $slotIndex): void
    {
        $subjectId = $this->getOrCreateSubjectId($subject);
        $roomId    = $this->getOrCreateRoomId($room);

        $this->db->execute(
            'INSERT INTO timetable (user_id, weekday, time, subject_id, room_id, slot_index)'
            . ' VALUES (?, ?, ?, ?, ?, ?)',
            [$userId, $weekday, $time, $subjectId, $roomId, $slotIndex]
        );
    }

    public function getTimetableByDay(int $userId, string $weekday): array
    {
        return $this->db->execute(
            'SELECT t.*, s.name AS subject, r.name AS room FROM timetable t LEFT JOIN subjects s ON t.subject_id = s.id LEFT JOIN rooms r ON t.room_id = r.id
             WHERE t.user_id = ? AND t.weekday = ?
             ORDER BY t.slot_index',
            [$userId, $weekday],
            true
        );
    }

    private function getOrCreateSubjectId(string $subject): ?int
    {
        if (trim($subject) === '') {
            return null;
        }

        $id = $this->db->fetchValue('SELECT id FROM subjects WHERE name = ?', [$subject]);

        if ($id) {
            return (int) $id;
        }

        $this->db->execute('INSERT INTO subjects (name) VALUES (?)', [$subject]);
        return (int) $this->db->lastInsertId();
    }

    private function getOrCreateRoomId(string $room): ?int
    {
        if (trim($room) === '') {
            return null;
        }

        $id = $this->db->fetchValue('SELECT id FROM rooms WHERE name = ?', [$room]);

        if ($id) {
            return (int) $id;
        }

        $this->db->execute('INSERT INTO rooms (name) VALUES (?)', [$room]);
        return (int) $this->db->lastInsertId();
    }

    public function fetchAllWeekdays(): array
    {
        return $this->db->execute('SELECT id, day_name FROM weekdays ORDER BY id', [], true);
    }

    public function fetchAllTimeSlots(): array
    {
        return $this->db->execute('SELECT id, start_time, end_time FROM time_slots ORDER BY id', [], true);
    }

    public function fetchUserSchedule(int $userId): array
    {
        $rows = $this->db->execute(
            'SELECT weekday_id, time_slot_id, course_name AS subject, room
             FROM user_schedules
             WHERE user_id = ?',
            [$userId],
            true
        );

        $timetable = [];
        foreach ($rows as $row) {
            $timetable[$row["weekday_id"]][$row["time_slot_id"]] = [
                'subject' => $row['subject'],
                'room' => $row['room'],
            ];
        }

        return $timetable;
    }

    public function saveUserSchedule(int $userId, array $schedule): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->execute('DELETE FROM user_schedules WHERE user_id = ?', [$userId]);

            $stmt = $this->db->prepare('INSERT INTO user_schedules
                (user_id, course_name, weekday_id, time_slot_id, room)
                VALUES (?, ?, ?, ?, ?)');

            foreach ($schedule as $weekdayId => $slots) {
                foreach ($slots as $slotId => $entry) {
                    $subject = trim($entry['fach'] ?? '');
                    $room    = trim($entry['raum'] ?? '');

                    if ($subject === '') {
                        continue;
                    }

                    $stmt->execute([$userId, $subject, $weekdayId, $slotId, $room]);
                }
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
