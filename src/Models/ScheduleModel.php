<?php
require_once __DIR__ . '/Database.php';

class ScheduleModel
{
    public static function fetchUserSchedule(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT weekday_id, time_slot_id, course_name AS subject, room FROM user_schedules WHERE user_id = ?');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $timetable = [];
        foreach ($rows as $row) {
            $timetable[$row["weekday_id"]][$row["time_slot_id"]] = [
                'subject' => $row['subject'],
                'room'    => $row['room'],
            ];
        }
        return $timetable;
    }

    public static function saveUserSchedule(int $userId, array $schedule): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM user_schedules WHERE user_id = ?')->execute([$userId]);
            $stmt = $pdo->prepare('INSERT INTO user_schedules (user_id, course_name, weekday_id, time_slot_id, room) VALUES (?, ?, ?, ?, ?)');
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
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
