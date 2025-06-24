<?php
require_once __DIR__ . '/../Models/ScheduleModel.php';

class ScheduleController
{
    public function getSchedule(int $userId): array
    {
        return ScheduleModel::fetchUserSchedule($userId);
    }

    public function saveSchedule(int $userId, array $schedule): void
    {
        ScheduleModel::saveUserSchedule($userId, $schedule);
    }
}
