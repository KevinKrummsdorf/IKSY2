<?php

declare(strict_types=1);

class TodoRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getTodosByUserId(int $userId): array
    {
        $query = '
            SELECT * FROM todos
            WHERE user_id = ?
            ORDER BY created_at DESC
        ';
        return $this->db->execute($query, [$userId], true);
    }

    public function insertTodo(int $userId, string $text, ?string $dueDate, string $priority = 'medium'): void
    {
        $query = '
            INSERT INTO todos (user_id, text, due_date, priority)
            VALUES (?, ?, ?, ?)
        ';
        $this->db->execute($query, [$userId, $text, $dueDate, $priority]);
    }

    public function getTodoStatus(int $todoId, int $userId): ?array
    {
        $query = '
            SELECT is_done FROM todos
            WHERE id = ? AND user_id = ?
        ';
        return $this->db->fetchOne($query, [$todoId, $userId]);
    }

    public function updateTodoStatus(int $todoId, int $userId, int $newStatus): void
    {
        $query = '
            UPDATE todos
            SET is_done = ?
            WHERE id = ? AND user_id = ?
        ';
        $this->db->execute($query, [$newStatus, $todoId, $userId]);
    }

    public function getTodosForDateRange(int $userId, string $startDate, string $endDate): array
    {
        $query = '
            SELECT id,
                   text AS title,
                   due_date,
                   priority
            FROM todos
            WHERE user_id = :uid
              AND due_date BETWEEN :start AND :end
            ORDER BY due_date
        ';
        return $this->db->execute($query, [
            ':uid'   => $userId,
            ':start' => $startDate,
            ':end'   => $endDate,
        ], true);
    }

    public function getTodosForDate(int $userId, string $date): array
    {
        return $this->getTodosForDateRange($userId, $date, $date);
    }

    public function updateTodoPriority(int $todoId, int $userId, string $priority): void
    {
        $query = '
            UPDATE todos
            SET priority = ?
            WHERE id = ? AND user_id = ? AND is_done = 0
        ';
        $this->db->execute($query, [$priority, $todoId, $userId]);
    }

    public function deleteTodo(int $todoId, int $userId): void
    {
        $query = '
            DELETE FROM todos
            WHERE id = ? AND user_id = ? AND is_done = 1
        ';
        $this->db->execute($query, [$todoId, $userId]);
    }

    public function deleteCompletedTodos(int $userId): void
    {
        $query = '
            DELETE FROM todos
            WHERE user_id = ? AND is_done = 1
        ';
        $this->db->execute($query, [$userId]);
    }
}
