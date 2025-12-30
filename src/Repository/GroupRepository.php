<?php

declare(strict_types=1);

class GroupRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function fetchGroupByUser(int $userId): ?array
    {
        $sql = '
            SELECT g.*
            FROM group_members gm
            JOIN groups g ON gm.group_id = g.id
            WHERE gm.user_id = :uid
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [':uid' => $userId]);
    }

    public function fetchGroupsByUser(int $userId): array
    {
        $sql = '
            SELECT g.*
            FROM group_members gm
            JOIN groups g ON gm.group_id = g.id
            WHERE gm.user_id = :uid
            ORDER BY g.name ASC
        ';
        return $this->db->execute($sql, [':uid' => $userId], true);
    }

    public function createGroup(string $groupName, int $userId, string $joinType = 'open', ?string $inviteCode = null): ?int
    {
        $this->db->beginTransaction();
        try {
            $sql = 'INSERT INTO groups (name, join_type, invite_code) VALUES (:name, :jtype, :icode)';
            $this->db->execute($sql, [
                ':name'  => $groupName,
                ':jtype' => $joinType,
                ':icode' => $inviteCode,
            ]);
            $groupId = (int)$this->db->lastInsertId();

            $this->db->execute('INSERT INTO group_members (group_id, user_id) VALUES (:gid, :uid)', [':gid' => $groupId, ':uid' => $userId]);
            $this->db->execute('INSERT INTO group_roles (group_id, user_id, role) VALUES (:gid, :uid, :role)', [':gid' => $groupId, ':uid' => $userId, ':role' => 'admin']);

            $this->db->commit();
            return $groupId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function fetchGroupByName(string $name): ?array
    {
        $sql = 'SELECT id, name, join_type, invite_code, group_picture FROM groups WHERE name = :name LIMIT 1';
        return $this->db->fetchOne($sql, [':name' => $name]);
    }

    public function addUserToGroup(int $groupId, int $userId): bool
    {
        $sql = '
            INSERT INTO group_members (group_id, user_id)
            VALUES (:gid, :uid)
            ON CONFLICT DO NOTHING
        ';
        return $this->db->execute($sql, [':gid' => $groupId, ':uid' => $userId]) > 0;
    }

    public function removeUserFromGroup(int $groupId, int $userId): bool
    {
        $this->db->execute('DELETE FROM group_roles WHERE group_id = :gid AND user_id = :uid', [':gid' => $groupId, ':uid' => $userId]);
        $sql = '
            DELETE FROM group_members
            WHERE group_id = :gid AND user_id = :uid
        ';
        return $this->db->execute($sql, [':gid' => $groupId, ':uid' => $userId]) > 0;
    }

    public function getGroupMembers(int $groupId): array
    {
        $sql = '
            SELECT
                u.id   AS user_id,
                u.username,
                u.email,
                gr.role
            FROM group_members gm
            JOIN users u ON gm.user_id = u.id
            LEFT JOIN group_roles gr
                ON gr.group_id = gm.group_id AND gr.user_id = gm.user_id
            WHERE gm.group_id = :gid
            ORDER BY
                CASE WHEN gr.role = \'admin\' THEN 0 ELSE 1 END,
                u.username ASC
        ';
        return $this->db->execute($sql, [':gid' => $groupId], true);
    }

    public function getUploadsByGroup(int $groupId): array
    {
        $sql = '
            SELECT u.id, u.stored_name, m.title
            FROM uploads u
            JOIN materials m ON u.material_id = m.id
            WHERE u.group_id = :gid AND u.is_approved = 1
            ORDER BY u.uploaded_at DESC
        ';
        return $this->db->execute($sql, [':gid' => $groupId], true);
    }

    public function fetchAllGroups(): array
    {
        $sql = 'SELECT id, name, group_picture FROM groups ORDER BY name ASC';
        return $this->db->execute($sql, [], true);
    }

    public function fetchGroupById(int $groupId): ?array
    {
        $sql = 'SELECT id, name, join_type, invite_code, group_picture FROM groups WHERE id = :gid LIMIT 1';
        return $this->db->fetchOne($sql, [':gid' => $groupId]);
    }

    public function fetchGroupByInviteCode(string $code): ?array
    {
        $sql = 'SELECT id, name, join_type, invite_code, group_picture FROM groups WHERE invite_code = :code LIMIT 1';
        return $this->db->fetchOne($sql, [':code' => $code]);
    }

    public function fetchUserRoleInGroup(int $groupId, int $userId): ?string
    {
        $sql = '
            SELECT role
            FROM group_roles
            WHERE group_id = :gid AND user_id = :uid
            LIMIT 1
        ';
        $row = $this->db->fetchOne($sql, [':gid' => $groupId, ':uid' => $userId]);
        return $row['role'] ?? null;
    }

    public function setUserRoleInGroup(int $groupId, int $userId, string $role): bool
    {
        $sql = '
            INSERT INTO group_roles (group_id, user_id, role)
            VALUES (:gid, :uid, :role)
            ON CONFLICT (group_id, user_id) DO UPDATE SET role = EXCLUDED.role
        ';
        return $this->db->execute($sql, [
            ':gid'  => $groupId,
            ':uid'  => $userId,
            ':role' => $role,
        ]) > 0;
    }

    public function deleteGroup(int $groupId): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->execute('DELETE FROM group_roles WHERE group_id = ?', [$groupId]);
            $this->db->execute('DELETE FROM group_members WHERE group_id = ?', [$groupId]);
            $this->db->execute('UPDATE uploads SET group_id = NULL WHERE group_id = ?', [$groupId]);
            $this->db->execute('DELETE FROM groups WHERE id = ?', [$groupId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function fetchActiveGroupInvite(int $groupId, int $userId): ?array
    {
        $sql = '
            SELECT * FROM group_invites
            WHERE group_id = :gid AND invited_user_id = :uid
              AND used_at IS NULL AND expires_at > NOW()
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [':gid' => $groupId, ':uid' => $userId]);
    }

    public function createGroupInvite(int $groupId, int $userId, string $token, int $expiresHours = 48): bool
    {
        if ($this->fetchActiveGroupInvite($groupId, $userId)) {
            return false;
        }

        $sql = '
            INSERT INTO group_invites (group_id, invited_user_id, token, created_at, expires_at)
            VALUES (:gid, :uid, :token, NOW(), NOW() + MAKE_INTERVAL(hours => :exp))
        ';
        return $this->db->execute($sql, [
            ':gid'  => $groupId,
            ':uid'  => $userId,
            ':token'=> $token,
            ':exp'  => $expiresHours,
        ]) > 0;
    }

    public function fetchGroupInviteByToken(string $token): ?array
    {
        $sql = '
            SELECT * FROM group_invites
            WHERE token = :token AND used_at IS NULL AND expires_at > NOW()
            LIMIT 1
        ';
        return $this->db->fetchOne($sql, [':token' => $token]);
    }

    public function markGroupInviteUsed(int $inviteId): void
    {
        $this->db->execute('UPDATE group_invites SET used_at = NOW() WHERE id = :id', [':id' => $inviteId]);
    }

    public function createGroupEvent(int $groupId, string $title, string $date, ?string $time, string $repeat = 'none'): bool
    {
        $sql = '
            INSERT INTO group_events (group_id, title, event_date, event_time, repeat_interval)
            VALUES (:gid, :title, :date, :time, :repeat)
        ';
        return $this->db->execute($sql, [
            ':gid'    => $groupId,
            ':title'  => $title,
            ':date'   => $date,
            ':time'   => $time,
            ':repeat' => $repeat,
        ]) > 0;
    }

    public function deleteGroupEvent(int $eventId, int $groupId): bool
    {
        $sql = 'DELETE FROM group_events WHERE id = :eid AND group_id = :gid';
        return $this->db->execute($sql, [
            ':eid' => $eventId,
            ':gid' => $groupId,
        ]) > 0;
    }

    public function getGroupEventsByGroup(int $groupId): array
    {
        $sql = '
            SELECT id, title, event_date, event_time, repeat_interval
            FROM group_events
            WHERE group_id = :gid
            ORDER BY event_date, event_time
        ';
        return $this->db->execute($sql, [':gid' => $groupId], true);
    }

    public function getGroupEventsForUserDateRange(int $userId, string $startDate, string $endDate): array
    {
        $sql = 'SELECT ge.title, ge.event_date, ge.event_time, ge.repeat_interval,
                       g.name AS group_name, g.group_picture
                FROM group_events ge
                JOIN group_members gm ON ge.group_id = gm.group_id
                JOIN groups g ON ge.group_id = g.id
                WHERE gm.user_id = :uid
                  AND ge.event_date <= :end
                ORDER BY ge.event_date, ge.event_time';
        $rows = $this->db->execute($sql, [
            ':uid'  => $userId,
            ':end'  => $endDate,
        ], true);
        $events = [];
        foreach ($rows as $row) {
            $events = array_merge(
                $events,
                $this->expandEventRow($row, $startDate, $endDate)
            );
        }

        return $events;
    }

    private function expandEventRow(array $row, string $startDate, string $endDate): array
    {
        $date     = new DateTimeImmutable($row['event_date']);
        $interval = $row['repeat_interval'] ?? 'none';
        $events   = [];

        while ($date->format('Y-m-d') < $startDate) {
            $next = $this->advanceRecurringDate($date, $interval);
            if ($next === null) {
                return $events;
            }
            $date = $next;
        }

        while ($date->format('Y-m-d') <= $endDate) {
            $events[] = [
                'title'         => $row['title'],
                'event_date'    => $date->format('Y-m-d'),
                'event_time'    => $row['event_time'],
                'group_name'    => $row['group_name'],
                'group_picture' => $row['group_picture'] ?? null,
            ];
            $next = $this->advanceRecurringDate($date, $interval);
            if ($next === null) {
                break;
            }
            $date = $next;
        }

        return $events;
    }

    private function advanceRecurringDate(DateTimeImmutable $date, string $interval): ?DateTimeImmutable
    {
        switch ($interval) {
            case 'weekly':
                return $date->modify('+1 week');
            case 'biweekly':
                return $date->modify('+2 weeks');
            case 'monthly':
                return $date->modify('+1 month');
            default:
                return null;
        }
    }

    public function updateGroup(int $groupId, array $fields): void
    {
        $set = [];
        $params = [':id' => $groupId];

        foreach ($fields as $key => $value) {
            $set[] = "\"$key\" = :$key";
            $params[":$key"] = $value;
        }

        if (empty($set)) {
            return;
        }

        $sql = 'UPDATE "groups" SET ' . implode(', ', $set) . ' WHERE id = :id';
        $this->db->execute($sql, $params);
    }
}
