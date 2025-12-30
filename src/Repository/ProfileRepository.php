<?php

declare(strict_types=1);

class ProfileRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getProfilesByUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "
            SELECT p.user_id, u.username, p.first_name, p.last_name, p.profile_picture
            FROM profile p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id IN ($placeholders)
        ";
        return $this->db->execute($sql, array_values($userIds), true);
    }

    public function fetchUserProfile(int $userId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM profile WHERE user_id = :user_id', ['user_id' => $userId]);
    }

    public function getOrCreateUserProfile(int $userId): array
    {
        $profile = $this->fetchUserProfile($userId);

        if (!$profile) {
            $this->db->execute('INSERT INTO profile (user_id) VALUES (:id)', [':id' => $userId]);
            $profile = $this->fetchUserProfile($userId);
        }

        return $profile ?: [];
    }

    public function updateUserProfile(int $userId, array $fields): void
    {
        $set = [];
        $params = [':id' => $userId];

        foreach ($fields as $key => $value) {
            // Use placeholders to prevent SQL injection
            $set[] = "\"$key\" = :$key";
            $params[":$key"] = $value;
        }

        if (empty($set)) {
            return;
        }

        $sql = 'UPDATE profile SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE user_id = :id';

        $this->db->execute($sql, $params);
    }
    
    public function getUserSocialMedia(int $userId): array
    {
        $sql = 'SELECT platform, username FROM social_media WHERE user_id = :uid';
        return $this->db->execute($sql, [':uid' => $userId], true);
    }

    public function saveUserSocialMedia(int $userId, string $platform, string $username): void
    {
        $existingId = $this->db->fetchValue(
            'SELECT id FROM social_media WHERE user_id = :uid AND platform = :platform',
            [':uid' => $userId, ':platform' => $platform]
        );

        if ($existingId) {
            if ($username === '') {
                $this->db->execute('DELETE FROM social_media WHERE id = :id', [':id' => $existingId]);
            } else {
                $this->db->execute(
                    'UPDATE social_media SET username = :uname WHERE id = :id',
                    [':uname' => $username, ':id' => $existingId]
                );
            }
        } elseif ($username !== '') {
            $this->db->execute(
                'INSERT INTO social_media (user_id, platform, username) VALUES (:uid, :platform, :uname)',
                [':uid' => $userId, ':platform' => $platform, ':uname' => $username]
            );
        }
    }
}
