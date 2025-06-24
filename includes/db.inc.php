<?php

declare(strict_types=1);
    require_once __DIR__ . '/../includes/logger.inc.php';

class DbFunctions
{
    private static ?PDO $pdo = null;    private static ?ILogger $log = null;

    private static function getLogger(): ILogger
    {
        if (self::$log === null) {
            self::$log = LoggerFactory::get('db');
        }
        return self::$log;
    }

    // Gibt die Gruppe zurück, in der der Nutzer Mitglied ist
    public static function fetchGroupByUser(int $userId): ?array
    {
        $sql = '
        SELECT g.*
        FROM group_members gm
        JOIN groups g ON gm.group_id = g.id
        WHERE gm.user_id = :uid
        LIMIT 1
    ';
        return self::fetchOne($sql, [':uid' => $userId]);
    }

    /**
     * Liefert alle Gruppen, in denen der Nutzer Mitglied ist.
     */
    public static function fetchGroupsByUser(int $userId): array
    {
        $sql = '
            SELECT g.*
            FROM group_members gm
            JOIN groups g ON gm.group_id = g.id
            WHERE gm.user_id = :uid
            ORDER BY g.name ASC
        ';
        return self::execute($sql, [':uid' => $userId], true);
    }

    // Legt eine neue Gruppe an und trägt den Nutzer als Mitglied ein
    public static function createGroup(
        string $groupName,
        int $userId,
        string $joinType = 'open',
        ?string $inviteCode = null
    ): ?int
    {
        $pdo = self::db_connect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO groups (name, join_type, invite_code) VALUES (:name, :jtype, :icode)'
            );
            $stmt->execute([
                ':name'  => $groupName,
                ':jtype' => $joinType,
                ':icode' => $inviteCode,
            ]);
            $groupId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO group_members (group_id, user_id) VALUES (:gid, :uid)');
            $stmt->execute([':gid' => $groupId, ':uid' => $userId]);

            $stmt = $pdo->prepare(
                'INSERT INTO group_roles (group_id, user_id, role) VALUES (:gid, :uid, :role)'
            );
            $stmt->execute([':gid' => $groupId, ':uid' => $userId, ':role' => 'admin']);

            $pdo->commit();
            return $groupId;
        } catch (Exception $e) {
            $pdo->rollBack();
            self::getLogger()->error('createGroup failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Holt eine Gruppe anhand ihres Namens
    public static function fetchGroupByName(string $name): ?array
    {
        $sql = 'SELECT id, name, join_type, invite_code FROM groups WHERE name = :name LIMIT 1';
        return self::fetchOne($sql, [':name' => $name]);
    }

    // Fügt den Benutzer einer Gruppe hinzu
    public static function addUserToGroup(int $groupId, int $userId): bool
    {
        $sql = '
        INSERT IGNORE INTO group_members (group_id, user_id)
        VALUES (:gid, :uid)
    ';
        return self::execute($sql, [':gid' => $groupId, ':uid' => $userId]) > 0;
    }

    // Entfernt einen Benutzer aus einer Gruppe
    public static function removeUserFromGroup(int $groupId, int $userId): bool
    {
        // Rolle entfernen
        self::execute(
            'DELETE FROM group_roles WHERE group_id = :gid AND user_id = :uid',
            [':gid' => $groupId, ':uid' => $userId]
        );

        $sql = '
        DELETE FROM group_members
        WHERE group_id = :gid AND user_id = :uid
    ';
        return self::execute($sql, [':gid' => $groupId, ':uid' => $userId]) > 0;
    }

    // Gibt alle Mitglieder einer Gruppe zurück (id, Username, E-Mail, Rolle)
    public static function getGroupMembers(int $groupId): array
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
            ORDER BY u.username ASC
        ';
        return self::execute($sql, [':gid' => $groupId], true);
    }

    // Gibt alle Uploads zurück, die einer Gruppe zugewiesen wurden
    public static function getUploadsByGroup(int $groupId): array
    {
        $sql = '
        SELECT u.id, u.stored_name, m.title
        FROM uploads u
        JOIN materials m ON u.material_id = m.id
        WHERE u.group_id = :gid AND u.is_approved = 1
        ORDER BY u.uploaded_at DESC
    ';
        return self::execute($sql, [':gid' => $groupId], true);
    }

    /**
     * Liefert alle Gruppen (id und Name)
     */
    public static function fetchAllGroups(): array
    {
        $sql = 'SELECT id, name FROM `groups` ORDER BY name ASC';
        return self::execute($sql, [], true);
    }

    /**
     * Holt eine Gruppe anhand ihrer ID.
     */
    public static function fetchGroupById(int $groupId): ?array
    {
        $sql = 'SELECT id, name, join_type, invite_code FROM `groups` WHERE id = :gid LIMIT 1';
        return self::fetchOne($sql, [':gid' => $groupId]);
    }

    /**
     * Holt eine Gruppe anhand ihres Einladungscodes.
     */
    public static function fetchGroupByInviteCode(string $code): ?array
    {
        $sql = 'SELECT id, name, join_type, invite_code FROM `groups` WHERE invite_code = :code LIMIT 1';
        return self::fetchOne($sql, [':code' => $code]);
    }

    /**
     * Liefert die Rolle eines Nutzers in einer Gruppe oder null.
     */
    public static function fetchUserRoleInGroup(int $groupId, int $userId): ?string
    {
        $sql = '
            SELECT role
            FROM group_roles
            WHERE group_id = :gid AND user_id = :uid
            LIMIT 1
        ';
        $row = self::fetchOne($sql, [':gid' => $groupId, ':uid' => $userId]);
        return $row['role'] ?? null;
    }

    /**
     * Setzt die Rolle eines Nutzers in einer Gruppe.
     */
    public static function setUserRoleInGroup(int $groupId, int $userId, string $role): bool
    {
        $sql = '
            INSERT INTO group_roles (group_id, user_id, role)
            VALUES (:gid, :uid, :role)
            ON DUPLICATE KEY UPDATE role = VALUES(role)
        ';
        return self::execute($sql, [
            ':gid'  => $groupId,
            ':uid'  => $userId,
            ':role' => $role,
        ]) > 0;
    }

    /**
     * Löscht eine Gruppe vollständig.
     */
    public static function deleteGroup(int $groupId): bool
    {
        $pdo = self::db_connect();

        $pdo->beginTransaction();
        try {
            // Abhängigkeiten entfernen
            $pdo->prepare('DELETE FROM group_roles WHERE group_id = ?')
                ->execute([$groupId]);
            $pdo->prepare('DELETE FROM group_members WHERE group_id = ?')
                ->execute([$groupId]);

            // Uploads der Gruppe lösen
            $pdo->prepare('UPDATE uploads SET group_id = NULL WHERE group_id = ?')
                ->execute([$groupId]);

            $pdo->prepare('DELETE FROM `groups` WHERE id = ?')
                ->execute([$groupId]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            self::getLogger()->error('deleteGroup failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Prüft, ob eine aktive Einladung für Benutzer und Gruppe existiert.
     */
    public static function fetchActiveGroupInvite(int $groupId, int $userId): ?array
    {
        $sql = 'SELECT * FROM group_invites
                WHERE group_id = :gid AND invited_user_id = :uid
                  AND used_at IS NULL AND expires_at > NOW()
                LIMIT 1';
        return self::fetchOne($sql, [':gid' => $groupId, ':uid' => $userId]);
    }

    /**
     * Erstellt eine neue Einladung f\xC3\xBCr eine Lerngruppe.
     */
    public static function createGroupInvite(int $groupId, int $userId, string $token, int $expiresHours = 48): bool
    {
        if (self::fetchActiveGroupInvite($groupId, $userId)) {
            return false;
        }

        $sql = 'INSERT INTO group_invites (group_id, invited_user_id, token, created_at, expires_at)
                VALUES (:gid, :uid, :token, NOW(), DATE_ADD(NOW(), INTERVAL :exp HOUR))';

        return self::execute($sql, [
            ':gid'  => $groupId,
            ':uid'  => $userId,
            ':token'=> $token,
            ':exp'  => $expiresHours,
        ]) > 0;
    }

    /**
     * Holt eine Einladung anhand ihres Tokens.
     */
    public static function fetchGroupInviteByToken(string $token): ?array
    {
        $sql = 'SELECT * FROM group_invites
                WHERE token = :token AND used_at IS NULL AND expires_at > NOW()
                LIMIT 1';
        return self::fetchOne($sql, [':token' => $token]);
    }

    /**
     * Markiert eine Einladung als benutzt.
     */
    public static function markGroupInviteUsed(int $inviteId): void
    {
        self::execute('UPDATE group_invites SET used_at = NOW() WHERE id = :id', [':id' => $inviteId]);
    }

    
 
    /**
     * Singleton-DB-Verbindung über Konfigurationsarray
     */
    public static function db_connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        global $config;
        $db = $config['db'] ?? null;
        if (
            empty($db) ||
            empty($db['host']) ||
            empty($db['name']) ||
            empty($db['user']) ||
            empty($db['pass'])
        ) {
            self::getLogger()->error('Fehlende DB-Konfiguration', [
                'config' => $db,
            ]);
            throw new RuntimeException('Fehlende Datenbank-Konfiguration in $config[\'db\'].');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'] ?? 3306,
            $db['name']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
            return self::$pdo;
        } catch (PDOException $e) {
            self::getLogger()->error('DB-Verbindungsfehler', [
                'dsn' => $dsn,
                'user' => $db['user'],
                'error' => $e->getMessage(),
            ]);

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => defined('DEBUG')
                    ? 'DB-Fehler: ' . $e->getMessage()
                    : 'Interner Serverfehler. Bitte später erneut versuchen.'
            ]);
            throw $e;
        }
    }

    // ==== ToDo-Funktionen ====

    /**
     * Holt alle ToDos eines Benutzers.
     */
    public static function getTodosByUserId(int $userId): array
    {
        $query = '
        SELECT * FROM todos
        WHERE user_id = ?
        ORDER BY created_at DESC
    ';
        return self::execute($query, [$userId], true);
    }

    /**
     * Legt ein neues ToDo an.
     */
    public static function insertTodo(int $userId, string $text, ?string $dueDate, string $priority = 'medium'): void
    {
        $query = '
        INSERT INTO todos (user_id, text, due_date, priority)
        VALUES (?, ?, ?, ?)
    ';
        self::execute($query, [$userId, $text, $dueDate, $priority]);
    }

    /**
     * Liefert den aktuellen Status eines ToDos.
     */
    public static function getTodoStatus(int $todoId, int $userId): ?array
    {
        $query = '
        SELECT is_done FROM todos
        WHERE id = ? AND user_id = ?
    ';
        return self::fetchOne($query, [$todoId, $userId]);
    }

    /**
     * Setzt den Status eines ToDos.
     */
    public static function updateTodoStatus(int $todoId, int $userId, int $newStatus): void
    {
        $query = '
        UPDATE todos
        SET is_done = ?
        WHERE id = ? AND user_id = ?
    ';
        self::execute($query, [$newStatus, $todoId, $userId]);
    }

    /**
    /* -----------------------------------------------------------------
     * ToDo-Funktionen (Datum / Status / Priorität)
     * ----------------------------------------------------------------- */

    /**
     * Liefert alle ToDos eines Benutzers innerhalb eines Datumsbereichs.
     */
    public static function getTodosForDateRange(
        int $userId,
        string $startDate,
        string $endDate
    ): array {
        $query = '
            SELECT id, title, due_date, priority
            FROM todos
            WHERE user_id = :uid
              AND due_date BETWEEN :start AND :end
            ORDER BY due_date
        ';
        return self::execute($query, [
            ':uid'   => $userId,
            ':start' => $startDate,
            ':end'   => $endDate,
        ], true);
    }

    /**
     * Liefert alle ToDos eines Benutzers an einem bestimmten Datum.
     */
    public static function getTodosForDate(int $userId, string $date): array
    {
        return self::getTodosForDateRange($userId, $date, $date);
    }

    /**
     * Aktualisiert die Priorität eines ToDos.
     */
    public static function updateTodoPriority(
        int $todoId,
        int $userId,
        string $priority
    ): void {
        $query = '
            UPDATE todos
            SET priority = ?
            WHERE id = ? AND user_id = ? AND is_done = 0
        ';
        self::execute($query, [$priority, $todoId, $userId]);
    }

    /**
     * Löscht ein erledigtes ToDo.
     */
    public static function deleteTodo(int $todoId, int $userId): void
    {
        $query = '
            DELETE FROM todos
            WHERE id = ? AND user_id = ? AND is_done = 1
        ';
        self::execute($query, [$todoId, $userId]);
    }

    /**
     * Löscht alle erledigten ToDos eines Benutzers.
     */
    public static function deleteCompletedTodos(int $userId): void
    {
        $query = '
            DELETE FROM todos
            WHERE user_id = ? AND is_done = 1
        ';
        self::execute($query, [$userId]);
    }

    

    /**Alle bestätigten Materialien abrufen**/

    public static function getApprovedUploads(): array
    {
        $query = '
        SELECT id, stored_name, material_id, uploaded_by
        FROM uploads
        WHERE is_approved = 1 AND group_id IS NULL
    ';
        return self::execute($query, [], true); // true = fetchAll()
    }
    
    /** Material abruf für "Material finden/suchen" **/
    public static function getAllMaterials(): array
{
    $query = '
        SELECT DISTINCT m.id, m.title, m.description, c.name AS course_name
        FROM materials m
        JOIN uploads u ON u.material_id = m.id
        JOIN courses c ON m.course_id = c.id
        WHERE u.is_approved = 1 AND u.group_id IS NULL
    ';
    return self::execute($query, [], true);
}

public static function getMaterialsByTitle(string $searchTerm): array
{
    $pdo = self::db_connect();
    $stmt = $pdo->prepare('
        SELECT DISTINCT m.id, m.title, m.description, c.name AS course_name
        FROM materials m
        JOIN uploads u ON u.material_id = m.id
        JOIN courses c ON m.course_id = c.id
        WHERE u.is_approved = 1 AND u.group_id IS NULL AND m.title LIKE :search
    ');
    $stmt->execute(['search' => '%' . $searchTerm . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public static function getAverageMaterialRating(int $materialId): ?array
    {
        $sql = 'SELECT AVG(rating) AS average_rating, COUNT(*) AS total_ratings FROM material_ratings WHERE material_id = :material_id';
        return self::fetchOne($sql, ['material_id' => $materialId]);
    }

    public static function getUserMaterialRating(int $materialId, int $userId): ?array
    {
        $sql = 'SELECT rating FROM material_ratings WHERE material_id = :material_id AND user_id = :user_id';
        return self::fetchOne($sql, ['material_id' => $materialId, 'user_id' => $userId]);
    }

    public static function getProfilesByUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $pdo = self::db_connect();
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "
            SELECT p.user_id, p.first_name, p.last_name, p.profile_picture, u.username
            FROM profile p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id IN ($placeholders)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($userIds));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Führt ein Prepared Statement aus und gibt Ergebnis oder Zeilenanzahl zurück.
     */
public static function execute(string $query, array $params = [], bool $expectResult = false): mixed
{
    $pdo = self::db_connect();
    $stmt = $pdo->prepare($query);

    if (!$stmt->execute($params)) {
        self::getLogger()->error('Fehler beim Ausführen des Statements', [
            'query'     => $query,
            'params'    => $params,
            'errorInfo' => $stmt->errorInfo(),
        ]);
        throw new RuntimeException('Fehler beim Ausführen des Statements.');
    }

    $rowCount = $stmt->rowCount();
    self::getLogger()->info('Statement erfolgreich', [
        'query'    => $query,
        'params'   => $params,
        'affected' => $rowCount,
    ]);

    return $expectResult ? $stmt->fetchAll() : $rowCount;
}

    /**
     * Holt die erste Zeile als assoziatives Array oder null.
     */
    public static function fetchOne(string $query, array $params = []): ?array
    {
        $pdo = self::db_connect();
        $stmt = self::db_connect()->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Holt einen einzelnen Feldwert (z. B. COUNT(*)) aus der ersten Zeile.
     */
    public static function fetchValue(string $query, array $params = []): mixed
    {
        $row = self::fetchOne($query, $params);
        return $row ? array_values($row)[0] : null;
    }

    /**
     * Holt ein Key-Value-Array aus 2-Spalten-Resultaten.
     */
    public static function fetchKeyValue(string $query, array $params = []): ?array
    {
        $pdo = self::db_connect();
        $stmt = self::db_connect()->prepare($query);
        $stmt->execute($params);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $result[(string)$row[0]] = $row[1];
        }

        return $result ?: null;
    }

        /**
         * INSERT für Uploads
         */
        public static function insertUpload(string $storedName,string $title,string $description,string $course): int {
        $sql = '
            INSERT INTO uploads
               (stored_name, title, description, course)
            VALUES
               (:stored_name, :title, :description, :course)
        ';
        self::getLogger()->info('INSERT Upload', [
            'stored_name' => $storedName,
            'title'       => $title,
            'description' => $description,
            'course'      => $course,
        ]);
        return self::execute($sql, [
            ':stored_name' => $storedName,
            ':title'       => $title,
            ':description' => $description,
            ':course'      => $course,
        ], false);
    }

    /**
     * INSERT für Upload-Logs
     */
public static function insertUploadLog(int $actedBy, int $uploadId): void
{
    self::logUploadAction($uploadId, 'upload', $actedBy, 'Neuer Upload eingereicht');
}





    /**
     * Holt den Benutzer anhand des Tokens für die E-Mail-Verifizierung.
     */
public static function fetchVerificationUser(string $token): ?array
{
    $sql = '
        SELECT u.id, u.username, uv.is_verified
        FROM verification_tokens vt
        JOIN users u ON u.id = vt.user_id
        JOIN user_verification uv ON u.id = uv.user_id
        WHERE vt.verification_token = :token
        LIMIT 1
    ';
    self::getLogger()->info('Fetch Verification User', [
        'token' => $token,
    ]);
    return self::fetchOne($sql, [':token' => $token]);
}


    /**
     * Update user to verified
     */
public static function verifyUser(int $userId): int
{
    $sql = 'UPDATE user_verification SET is_verified = TRUE WHERE user_id = :id';
    self::getLogger()->info('Verify User', ['user_id' => $userId]);
    return self::execute($sql, [':id' => $userId], false);
}

    /**
     * Markiert den Benutzer als nicht verifiziert.
     */
public static function unverifyUser(int $userId): int
{
    $sql = 'UPDATE user_verification SET is_verified = FALSE WHERE user_id = :id';
    self::getLogger()->info('Unverify User', ['user_id' => $userId]);
    return self::execute($sql, [':id' => $userId], false);
}

    /**
     * löscht den Verifizierungstoken
     */
    public static function deleteVerificationToken(int $userId): int
    {
        $sql = 'DELETE FROM verification_tokens WHERE user_id = :id';
        self::getLogger()->info('Delete Verification Token', [
            'user_id' => $userId,
        ]);
        return self::execute($sql, [':id' => $userId], false);
    }

    //startet eine Transaktion
        public static function beginTransaction(): void
    {
        self::db_connect()->beginTransaction();
    }

    //committet eine Transaktion
        public static function commit(): void
    {
        self::db_connect()->commit();
    }
    //rollback einer Transaktion
        public static function rollBack(): void
    {
        self::db_connect()->rollBack();
    }

    //holt die letzte ID
        public static function lastInsertId(): string
    {
        return self::db_connect()->lastInsertId();
    }

    // zählt die Anzahl der Einträge in einer Tabelle
    // mit einer bestimmten Bedingung
    private static array $allowedTables = ['users','roles','…'];
    private static array $allowedColumns = ['username','email','…'];
    public static function countWhere(string $table, string $column, mixed $value): int
    {
        if (!in_array($table, self::$allowedTables, true) ||
            !in_array($column, self::$allowedColumns, true)) {
            throw new InvalidArgumentException('Ungültige Tabelle oder Spalte.');
        }
        $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = :val";
        return (int) self::fetchValue($sql, [':val' => $value]);
    }

    //INSERT für die Benutzerregistrierung
public static function insertUser(string $username, string $email, string $passwordHash): int {
    $sql = '
        INSERT INTO users (username, email, password_hash)
        VALUES (:u, :e, :p)
    ';
    self::getLogger()->info('INSERT User', [
        'username' => $username,
        'email'    => $email,
        'password' => $passwordHash,
    ]);
    self::execute($sql, [
        ':u' => $username,
        ':e' => $email,
        ':p' => $passwordHash,
    ], false);

    $userId = (int)self::lastInsertId();

    // Init-Datensätze für Normalisierungstabellen
    self::execute('INSERT INTO user_verification (user_id) VALUES (:uid)', [':uid' => $userId]);
    self::execute('INSERT INTO user_security (user_id) VALUES (:uid)', [':uid' => $userId]);
    self::execute('INSERT INTO user_2fa (user_id) VALUES (:uid)', [':uid' => $userId]);

    return $userId;
}


    //setzt Rollen für einen Benutzer
    public static function assignRole(int $userId, int $roleId): int{
        $sql = '
            INSERT INTO user_roles (user_id, role_id)
            VALUES (:uid, :rid)
        ';
        self::getLogger()->info('Assign Role', [
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
        return self::execute($sql, [
            ':uid' => $userId,
            ':rid' => $roleId,
        ], false);
    }

    //holt den Benutzer anhand email oder username
public static function fetchUserByIdentifier(string $input): ?array
{
    $sql = '
        SELECT
            u.id,
            u.username,
            u.email,
            u.password_hash,
            uv.is_verified,
            r.role_name AS role
        FROM users u
        JOIN user_verification uv ON u.id = uv.user_id
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id
        WHERE u.username = :identUser OR u.email = :identEmail
        LIMIT 1
    ';
    self::getLogger()->info('Fetch User by Identifier', [
        'input' => $input,
    ]);
    self::getLogger()->info('SQL for fetchUserByIdentifier', ['sql' => $sql]);

    return self::fetchOne($sql, [
        ':identUser'  => $input,
        ':identEmail' => $input,
    ]);
}

    //UPDATE für last_login
    public static function updateLastLogin(int $userId): int
    {
        $sql = 'UPDATE users SET last_login = NOW() WHERE id = :id';
        self::getLogger()->info('Update Last Login', [
            'user_id' => $userId,
        ]);
        return self::execute($sql, [':id' => $userId], false);
    }

    //INSERT für die Login-Logs
    public static function insertLoginLog(?int $userId, string $ipAddress, bool $success, ?string $reason = null): int
    {
        $sql = '
            INSERT INTO login_logs (user_id, ip_address, success, reason)
            VALUES (:uid, :ip, :succ, :reason)
        ';
        self::getLogger()->info('INSERT Login Log', [
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'success' => $success,
            'reason' => $reason,
        ]);
        return self::execute($sql, [
            ':uid'    => $userId,
            ':ip'     => $ipAddress,
            ':succ'   => $success ? 1 : 0,
            ':reason' => $reason,
        ], false);
    }

        /**
     * Speichert ein verschlüsseltes 2FA-Secret und aktiviert 2FA für den Benutzer.
     */
public static function storeTwoFASecret(string $username, string $encryptedSecret): void
{
    $userId = self::fetchValue('SELECT id FROM users WHERE username = :u', [':u' => $username]);
    $sql = '
        UPDATE user_2fa 
        SET twofa_secret = :secret, is_twofa_enabled = 1
        WHERE user_id = :id
    ';
    self::getLogger()->info('Store 2FA Secret', ['username' => $username]);        
    self::execute($sql, [
        ':secret' => $encryptedSecret,
        ':id'     => $userId,
    ]);
}

    /**
     * Ruft das verschlüsselte 2FA-Secret eines Benutzers ab.
     */
public static function getTwoFASecret(string $username): ?string
{
    $sql = '
        SELECT u2fa.twofa_secret
        FROM users u
        JOIN user_2fa u2fa ON u.id = u2fa.user_id
        WHERE u.username = :username AND u2fa.is_twofa_enabled = 1
    ';
    self::getLogger()->info('Fetch 2FA Secret', ['username' => $username]);
    return self::fetchValue($sql, [':username' => $username]);
}

    /**
     * Prüft, ob für einen Benutzer 2FA aktiviert ist.
     */
public static function isTwoFAEnabled(string $username): bool
{
    $sql = '
        SELECT u2fa.is_twofa_enabled
        FROM users u
        JOIN user_2fa u2fa ON u.id = u2fa.user_id
        WHERE u.username = :username
    ';
    self::getLogger()->info('Check 2FA Enabled', ['username' => $username]);
    return (bool) self::fetchValue($sql, [':username' => $username]);
}

    /**
     * Deaktiviert 2FA für einen Benutzer.
     */
public static function disableTwoFA(string $username): void
{
    $userId = self::fetchValue('SELECT id FROM users WHERE username = :u', [':u' => $username]);
    $sql = '
        UPDATE user_2fa 
        SET twofa_secret = NULL, is_twofa_enabled = 0
        WHERE user_id = :id
    ';
    self::getLogger()->info('Disable 2FA', ['username' => $username]);
    self::execute($sql, [':id' => $userId]);
}

    /**
     * erhöt die Anzahl der fehlgeschlagenen Anmeldeversuche
     */
public static function updateFailedAttempts(int $userId, int $incrementBy = 1): int
{
    $sql = '
        UPDATE user_security
        SET failed_attempts = failed_attempts + :inc
        WHERE user_id = :id
    ';
    self::getLogger()->info('Update Failed Attempts', [
        'user_id' => $userId,
        'increment' => $incrementBy,
    ]);
    return self::execute($sql, [
        ':inc' => $incrementBy,
        ':id'  => $userId,
    ], false);
}

    /**
     * Sperrt das Benutzerkonto für eine bestimmte Zeit.
     * Standardmäßig 15 Minuten.
     */
public static function lockAccount(int $userId, int $lockMinutes = 15): int
{
    $sql = '
        UPDATE user_security
        SET account_locked = 1
        WHERE user_id = :id
    ';
    self::getLogger()->info('Lock Account', [
        'user_id' => $userId,
        'minutes' => $lockMinutes,
    ]);
    return self::execute($sql, [
        ':mins' => $lockMinutes,
        ':id'   => $userId,
    ], false);
}

    /**
     * Setzt die Anzahl der fehlgeschlagenen Anmeldeversuche zurück.
     */
public static function resetFailedAttempts(int $userId): int
{
    $sql = '
        UPDATE user_security
        SET failed_attempts = 0, account_locked = 0
        WHERE user_id = :id
    ';
    self::getLogger()->info('Reset Failed Attempts', [
        'user_id' => $userId,
    ]);
    return self::execute($sql, [':id' => $userId], false);
}

    /**
     * Überprüft, ob das Benutzerkonto gesperrt ist.
     */
public static function isAccountLocked(int $userId): bool
{
    $sql = '
        SELECT account_locked
        FROM user_security
        WHERE user_id = :id
    ';
    $locked = self::fetchValue($sql, [':id' => $userId]);
    return (bool)$locked;
}

    /**
     * Entsperrt das Benutzerkonto manuell durch Administrator.
     */
public static function unlockAccount(int $userId): int
{
    $sql = '
        UPDATE user_security
        SET account_locked = 0, failed_attempts = 0
        WHERE user_id = :user_id
    ';
    self::getLogger()->info('Unlock Account SQL', [
        'sql' => $sql,
        'params' => ['user_id' => $userId],
    ]);

    return self::execute($sql, [':user_id' => $userId]);
}


    /**
     * holt die letzten Login-Logs (admin-only)
     */
public static function fetchLoginLogs(bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $sql = '
        SELECT
            ll.user_id,
            u.username,
            ll.ip_address,
            ll.success,
            ll.reason,
            ll.created_at
        FROM login_logs AS ll
        LEFT JOIN users AS u ON ll.user_id = u.id
        ORDER BY ll.created_at DESC
        LIMIT :limit
    ';

    $stmt = self::db_connect()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Holt die letzten Captcha-Logs (admin-only)
 * Gibt ein Array mit Captcha-Log-Einträgen zurück.
 */
public static function fetchCaptchaLogs(bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $sql = '
        SELECT
            id,
            token,
            success,
            score,
            action,
            hostname,
            error_reason,
            created_at
        FROM captcha_log
        ORDER BY created_at DESC
        LIMIT :limit
    ';

    $stmt = self::db_connect()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Holt alle gesperrten Benutzer (admin-only)
 * Gibt ein Array mit Benutzerinformationen zurück.
 */
public static function fetchLockedUsers(bool $isAdmin, int $limit = 50): array
{
    if (!$isAdmin) {
        return [];
    }

    $sql = '
        SELECT u.id, u.username, u.email, us.failed_attempts
        FROM users u
        JOIN user_security us ON u.id = us.user_id
        WHERE us.account_locked = 1
        ORDER BY us.failed_attempts DESC
        LIMIT :limit
    ';

    $stmt = self::db_connect()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Gibt die letzten Kontaktanfragen zurück (admin-only)
 */
public static function getRecentContactRequests(int $limit = 100): array
{
    $sql = '
        SELECT contact_id, name, email, subject, created_at
        FROM contact_requests
        ORDER BY created_at DESC
        LIMIT :limit
    ';

    $stmt = self::db_connect()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Löscht eine bestimmte Kontaktanfrage anhand ihrer ID
 */
public static function deleteContactRequest(string $contactId): void
{
    $sql = '
        DELETE FROM contact_requests
        WHERE contact_id = :id
    ';

    $stmt = self::db_connect()->prepare($sql);
    $stmt->bindValue(':id', $contactId, PDO::PARAM_STR);
    $stmt->execute();
}
/* * Zählt die Anzahl der Login-Logs.
 * Gibt die Gesamtanzahl der Login-Logs zurück.
 */
public static function countLoginLogs(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM login_logs');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
/* * Holt eine Seite von Login-Logs mit Paginierung.
 * @param int $limit Anzahl der Einträge pro Seite
 * @param int $offset Offset für die Paginierung
 * @param bool $isAdmin Ist der Benutzer ein Administrator?
 * @return array Liste der Login-Logs
 */
public static function getLoginLogsPage(int $limit, int $offset, bool $isAdmin): array
{
    if (!$isAdmin) {
        return [];
    }

    $stmt = self::db_connect()->prepare('
        SELECT ll.user_id, u.username, ll.ip_address, ll.success, ll.reason, ll.created_at
        FROM login_logs AS ll
        LEFT JOIN users AS u ON ll.user_id = u.id
        ORDER BY ll.created_at DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* * Zählt die Anzahl der Captcha-Logs.
 * Gibt die Gesamtanzahl der Captcha-Logs zurück.
 */
public static function countCaptchaLogs(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM captcha_log');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
/* * Holt eine Seite von Captcha-Logs mit Paginierung.
 * @param int $limit Anzahl der Einträge pro Seite
 * @param int $offset Offset für die Paginierung
 * @return array Liste der Captcha-Logs
 */
public static function getCaptchaLogsPage(int $limit, int $offset): array
{
    $stmt = self::db_connect()->prepare('
        SELECT id, token, success, score, action, hostname, error_reason, created_at
        FROM captcha_log
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* * Zählt die Anzahl der Kontaktanfragen.
 * Gibt die Gesamtanzahl der Kontaktanfragen zurück.
 */
public static function countContactRequests(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM contact_requests');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
/* * Holt eine Seite von Kontaktanfragen mit Paginierung.
 * @param int $limit Anzahl der Einträge pro Seite
 * @param int $offset Offset für die Paginierung
 * @return array Liste der Kontaktanfragen
 */
public static function getContactRequestsPage(int $limit, int $offset): array
{
    $stmt = self::db_connect()->prepare('
        SELECT contact_id, name, email, subject, created_at
        FROM contact_requests
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* * Zählt die Anzahl der gesperrten Benutzer.
 * Gibt die Anzahl der Benutzer zurück, deren Konto gesperrt ist.
 */
public static function countLockedUsers(): int
{
    $stmt = self::db_connect()->prepare('
        SELECT COUNT(*) 
        FROM user_security 
        WHERE account_locked = 1
    ');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
/* * Holt eine Seite von gesperrten Benutzern mit Paginierung.
 * @param int $limit Anzahl der Einträge pro Seite
 * @param int $offset Offset für die Paginierung
 * @return array Liste der gesperrten Benutzer
 */
public static function getLockedUsersPage(int $limit, int $offset): array
{
    $stmt = self::db_connect()->prepare('
        SELECT u.id, u.username, u.email, us.failed_attempts
        FROM users u
        JOIN user_security us ON u.id = us.user_id
        WHERE us.account_locked = 1
        ORDER BY us.failed_attempts DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* * Holt alle gesperrten Benutzer mit Details.
 * Gibt ein Array mit Benutzerinformationen zurück.
 */
public static function getAllLockedUsers(): array
{
    $stmt = self::db_connect()->prepare('
        SELECT u.id, u.username, u.email, us.failed_attempts
        FROM users u
        JOIN user_security us ON u.id = us.user_id
        WHERE us.account_locked = 1
        ORDER BY us.failed_attempts DESC
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* * Holt die Kurs-ID anhand des Kursnamens.
 * Gibt null zurück, wenn der Kurs nicht gefunden wurde.
 */
public static function getUserById(int $userId): ?array
{
    $sql = 'SELECT username, email FROM users WHERE id = :id';
    $stmt = self::db_connect()->prepare($sql);
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
/**
 * Fügt einen neuen Upload in die Datenbank ein.
 * Gibt die ID des neuen Uploads zurück.
 */
public static function uploadFile(
    string $storedName,
    int $materialId,
    int $userId,
    ?int $groupId = null,
    bool $autoApprove = false
): int {
    $pdo = self::db_connect();

    $stmt = $pdo->prepare(
        "INSERT INTO uploads (stored_name, material_id, uploaded_by, uploaded_at, is_approved, group_id)
         VALUES (:storedName, :materialId, :userId, NOW(), :approved, :groupId)"
    );
    $stmt->execute([
        ':storedName' => $storedName,
        ':materialId' => $materialId,
        ':userId'     => $userId,
        ':approved'   => $autoApprove ? 1 : 0,
        ':groupId'    => $groupId,
    ]);

    return (int)$pdo->lastInsertId();
}

/*
    * Genehmigt einen Upload und erstellt ggf. ein neues Material.
    * Gibt true zurück, wenn erfolgreich.
*/
public static function approveUpload(int $uploadId, int $adminId): bool
{
    $pdo = self::db_connect();

    // Upload-Details holen
    $upload = $pdo->prepare("
        SELECT u.*, m.title, m.description, m.course_id
        FROM uploads u
        JOIN materials m ON u.material_id = m.id
        WHERE u.id = ?
    ");
    $upload->execute([$uploadId]);
    $data = $upload->fetch();

    if (!$data) {
        throw new RuntimeException("Upload $uploadId nicht gefunden oder unvollständig.");
    }

    // Material nur anlegen, wenn noch nicht vorhanden
    $checkStmt = $pdo->prepare("
        SELECT id FROM materials 
        WHERE course_id = ? AND title = ?
    ");
    $checkStmt->execute([$data['course_id'], $data['title']]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        $insertMaterial = $pdo->prepare("
            INSERT INTO materials (course_id, title, description)
            VALUES (?, ?, ?)
        ");
        $insertMaterial->execute([
            $data['course_id'],
            $data['title'],
            $data['description'] ?? null,
        ]);

        // Neue material_id ggf. aktualisieren
        $newMaterialId = (int)$pdo->lastInsertId();
        $updateUpload = $pdo->prepare("UPDATE uploads SET material_id = ? WHERE id = ?");
        $updateUpload->execute([$newMaterialId, $uploadId]);
    }

    // Upload freigeben
    $stmt = $pdo->prepare("UPDATE uploads SET is_approved = 1 WHERE id = ?");
    $stmt->execute([$uploadId]);

    // Logging
    self::logUploadAction($uploadId, 'approved', $adminId, 'Upload freigegeben');
    return true;
}

/*
    * Protokolliert eine Aktion für einen Upload.
    * $uploadId: ID des Uploads
    * $action: Art der Aktion (z.B. 'approved', 'rejected')
    * $actedBy: ID des Benutzers, der die Aktion ausgeführt hat
    * $note: Optionaler Kommentar zur Aktion
*/
public static function logUploadAction(int $uploadId, string $action, int $actedBy, ?string $note = null): void
{
    $pdo = self::db_connect();

    $stmt = $pdo->prepare("
        INSERT INTO upload_logs (upload_id, action, acted_by, note)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$uploadId, $action, $actedBy, $note]);
}

/**
 * Holt eine Seite von Upload-Logs mit Paginierung.
 * @param int $limit Anzahl der Einträge pro Seite
 * @param int $offset Offset für die Paginierung
 * @param bool $isAdmin Ist der Benutzer ein Administrator?
 * @param bool $isMod Ist der Benutzer ein Moderator?
 * @return array Liste der Upload-Logs
 */
public static function getUploadLogsPage(int $limit, int $offset, bool $isAdmin, bool $isMod): array
{
    $pdo = self::db_connect();

    $stmt = $pdo->prepare("
        SELECT ul.id AS log_id, ul.upload_id, ul.action, ul.action_time, ul.note,
               u.stored_name, m.title, c.name AS course_name,
               us.username AS acted_by_user
        FROM upload_logs ul
        JOIN uploads u ON ul.upload_id = u.id
        JOIN materials m ON u.material_id = m.id
        JOIN courses c ON m.course_id = c.id
        LEFT JOIN users us ON ul.acted_by = us.id
        ORDER BY ul.action_time DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Zählt die Gesamtanzahl der Upload-Logs.
 * @return int Anzahl der Upload-Logs
 */
public static function countUploadLogs(): int
{
    $pdo = self::db_connect();
    return (int)$pdo->query("SELECT COUNT(*) FROM upload_logs")->fetchColumn();
}
/*
 * Lehnt einen Upload ab und protokolliert die Aktion.
 * Gibt true zurück, wenn erfolgreich.
 */
public static function rejectUpload(int $uploadId, int $modId, ?string $note = null): bool
{
    if ($note === null || trim($note) === '') {
        throw new InvalidArgumentException('Ablehnungsgrund erforderlich');
    }

    $pdo = self::db_connect();

    // Upload als abgelehnt markieren
    $stmt = $pdo->prepare("UPDATE uploads SET is_rejected = 1 WHERE id = ?");
    $stmt->execute([$uploadId]);

    // Logeintrag
    self::logUploadAction($uploadId, 'rejected', $modId, $note ?? 'Upload abgelehnt');

    return true;
}


/**
 * Holt alle ausstehenden Uploads, die noch nicht genehmigt oder abgelehnt wurden.
 * @return array Liste der ausstehenden Uploads
 */
public static function getPendingUploads(): array
{
    $pdo = self::db_connect();

    $stmt = $pdo->query("
        SELECT u.*, us.username, m.title, m.description, c.name AS course_name
        FROM uploads u
        LEFT JOIN users us ON u.uploaded_by = us.id
        LEFT JOIN materials m ON u.material_id = m.id
        LEFT JOIN courses c ON m.course_id = c.id
        WHERE u.is_approved = 0 AND u.is_rejected = 0
        ORDER BY u.uploaded_at DESC
    ");

    return $stmt->fetchAll();
}

/**
 * Holt die Details eines bestimmten Uploads anhand seiner ID.
 * @param int $uploadId ID des Uploads
 * @return array|null Details des Uploads oder null, wenn nicht gefunden
 */
public static function getUploadDetails(int $uploadId): ?array
{
    $pdo = self::db_connect();

    $stmt = $pdo->prepare("
        SELECT u.id, u.stored_name, u.uploaded_at,
               us.username, us.email,
               m.title, c.name AS course_name
        FROM uploads u
        JOIN materials m ON u.material_id = m.id
        JOIN courses c ON m.course_id = c.id
        LEFT JOIN users us ON u.uploaded_by = us.id
        WHERE u.id = ?
    ");
    $stmt->execute([$uploadId]);

    $result = $stmt->fetch();
    return $result !== false ? $result : null;
}
/**
 * Holt den Dateinamen eines genehmigten Uploads anhand seiner ID.
 * Gibt null zurück, wenn der Upload nicht genehmigt wurde oder nicht existiert.
 */
public static function getApprovedUploadById(int $uploadId): ?array
{
    $pdo = self::db_connect();

    $stmt = $pdo->prepare("
        SELECT stored_name
        FROM uploads
        WHERE id = ? AND is_approved = 1
        LIMIT 1
    ");
    $stmt->execute([$uploadId]);

    return $stmt->fetch() ?: null;
}
/**
 * Holt alle Kurse als Key-Value-Paar (name als value und name als name).
 * @return array Liste der Kurse
 */
public static function getAllCourses(): array
{
    $pdo = self::db_connect();

    $stmt = $pdo->query("SELECT name AS value, name AS name FROM courses ORDER BY name ASC");
    return $stmt->fetchAll();
}
/* * Holt alle ausstehenden Kursempfehlungen, die noch nicht genehmigt wurden.
 * @return array Liste der ausstehenden Kursempfehlungen
 */
public static function getPendingCourseSuggestions(): array
{
    $pdo = self::db_connect();
    $stmt = $pdo->query("
        SELECT pcs.*, u.username
        FROM pending_course_suggestions pcs
        JOIN users u ON pcs.user_id = u.id
        WHERE pcs.is_approved IS NULL
        ORDER BY pcs.suggested_at DESC
    ");
    return $stmt->fetchAll();
}

public static function getFilteredUploadLogs(array $filters, ?int $limit = null, ?int $offset = null): array
{
    $pdo = self::db_connect();

    $sql = "SELECT * FROM upload_logs WHERE 1=1";
    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND user_id = ?";
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['filename'])) {
        $sql .= " AND stored_name LIKE ?";
        $params[] = '%' . $filters['filename'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND created_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND created_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $sql .= " ORDER BY created_at DESC";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

    /**
     * Holt alle genehmigten Uploads eines Benutzers.
     *
     * @param int $userId ID des Benutzers
     * @return array Liste der Uploads mit Material- und Kursinformationen
     */
    public static function getApprovedUploadsByUser(int $userId): array
    {
        $pdo = self::db_connect();

        $stmt = $pdo->prepare(
            "SELECT u.id, u.stored_name, u.uploaded_at, m.title, c.name AS course_name
             FROM uploads u
             JOIN materials m ON u.material_id = m.id
             JOIN courses c ON m.course_id = c.id
             WHERE u.uploaded_by = ?
               AND u.is_approved = 1
             ORDER BY u.uploaded_at DESC"
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Löscht einen Upload des angegebenen Nutzers und gibt den Dateinamen
     * zurück. Gibt null zurück, wenn der Upload nicht gefunden wurde.
     */
    public static function deleteUpload(int $uploadId, int $userId): ?string
    {
        $pdo = self::db_connect();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT stored_name, material_id FROM uploads WHERE id = ? AND uploaded_by = ?');
            $stmt->execute([$uploadId, $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $pdo->rollBack();
                return null;
            }

            $name = $row['stored_name'];
            $materialId = (int)$row['material_id'];

            // Erst loggen, dann löschen, damit Foreign Keys nicht scheitern
            self::logUploadAction($uploadId, 'deleted', $userId, 'Upload gelöscht');

            $del = $pdo->prepare('DELETE FROM uploads WHERE id = ? AND uploaded_by = ?');
            $del->execute([$uploadId, $userId]);

            // Material entfernen, wenn keine Uploads mehr darauf verweisen
            $check = $pdo->prepare('SELECT COUNT(*) FROM uploads WHERE material_id = ?');
            $check->execute([$materialId]);
            if ((int)$check->fetchColumn() === 0) {
                $pdo->prepare('DELETE FROM materials WHERE id = ?')->execute([$materialId]);
            }

            $pdo->commit();
            return $name;
        } catch (Exception $e) {
            $pdo->rollBack();
            self::getLogger()->error('deleteUpload failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
/**
 * Zählt die Anzahl der Upload-Logs mit erweiterten Filtermöglichkeiten.
 * @param array $filters Filterkriterien
 * @return int Anzahl der gefilterten Upload-Logs
 */
public static function countFilteredUploadLogs(array $filters): int
{
    $pdo = self::db_connect();

    $sql = "SELECT COUNT(*) FROM upload_logs WHERE 1=1";
    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND user_id = ?";
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['filename'])) {
        $sql .= " AND stored_name LIKE ?";
        $params[] = '%' . $filters['filename'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND created_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND created_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}
/* * Holt die ID eines Kurses anhand seines Namens.
 * Gibt die Kurs-ID zurück oder wirft eine Ausnahme, wenn der Kurs nicht gefunden wird.
 */
public static function getCourseIdByName(string $name): int
{
    $pdo = self::db_connect();
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE name = ?");
    $stmt->execute([$name]);
    $course = $stmt->fetch();
    if (!$course) {
        throw new RuntimeException("Kurs nicht gefunden: $name");
    }
    return (int)$course['id'];
}
/**
 * Holt die ID eines Materials anhand
 */
public static function getOrCreateMaterial(int $courseId, string $title, string $desc): int
{
    $pdo = self::db_connect();
    $stmt = $pdo->prepare("SELECT id FROM materials WHERE course_id = ? AND title = ?");
    $stmt->execute([$courseId, $title]);
    $material = $stmt->fetch();

    if ($material) {
        return (int)$material['id'];
    }

    $stmt = $pdo->prepare("INSERT INTO materials (course_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$courseId, $title, $desc]);
    return (int)$pdo->lastInsertId();
}
/**
 * Reicht eine Kursempfehlung ein.
 * @param string $courseName Name des Kurses
 * @param int $userId ID des Benutzers, der die Empfehlung einreicht
 */
public static function submitCourseSuggestion(string $courseName, int $userId): void
{
    $pdo = self::db_connect();
    $stmt = $pdo->prepare("INSERT INTO pending_course_suggestions (course_name, user_id) VALUES (?, ?)");
    $stmt->execute([$courseName, $userId]);
}
/* * Zählt die Anzahl der Einträge in der Tabelle upload_logs
 * mit erweiterten Filtermöglichkeiten.
 */
public static function countExtendedUploadLogs(array $filters = []): int
{
    $pdo = self::db_connect();

    $sql = "
        SELECT COUNT(*)
        FROM upload_logs ul
        LEFT JOIN uploads up ON ul.upload_id = up.id
        LEFT JOIN materials m ON up.material_id = m.id
        LEFT JOIN courses c ON m.course_id = c.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND ul.acted_by = ?";
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['filename'])) {
        $sql .= " AND up.stored_name LIKE ?";
        $params[] = '%' . $filters['filename'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND ul.action_time >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND ul.action_time <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    if (!empty($filters['course_name'])) {
        $sql .= " AND c.name LIKE ?";
        $params[] = '%' . $filters['course_name'] . '%';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

/**
 * Holt die erweiterten Upload-Logs mit Filter- und Paginierungsoptionen.
 * @param array $filters Filterkriterien
 * @param int|null $limit Anzahl der Einträge pro Seite
 * @param int|null $offset Offset für die Paginierung
 * @return array Liste der erweiterten Upload-Logs
 */
public static function getExtendedUploadLogs(array $filters = [], ?int $limit = null, ?int $offset = null): array
{
    $pdo = self::db_connect();

$sql = "
    SELECT 
        ul.id AS log_id,
        ul.upload_id,
        ul.action,
        ul.acted_by,
        ul.note,
        ul.action_time,
        up.stored_name,
        u.username,
        c.name AS course_name
    FROM upload_logs ul
    LEFT JOIN uploads up ON ul.upload_id = up.id
    LEFT JOIN users u ON ul.acted_by = u.id
    LEFT JOIN materials m ON up.material_id = m.id
    LEFT JOIN courses c ON m.course_id = c.id
    WHERE 1=1
";

    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND ul.acted_by = ?";
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['filename'])) {
        $sql .= " AND up.stored_name LIKE ?";
        $params[] = '%' . $filters['filename'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND ul.action_time >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND ul.action_time <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    if (!empty($filters['course_name'])) {
        $sql .= " AND c.name LIKE ?";
        $params[] = '%' . $filters['course_name'] . '%';
    }

    $sql .= " ORDER BY ul.action_time DESC";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Zählt die Login-Logs basierend auf den angegebenen Filtern.
 * @param array $filters Filterkriterien
 * @return int Anzahl der Login-Logs
 */
public static function countFilteredLoginLogs(array $filters = []): int
{
    $pdo = self::db_connect();

    $sql = "SELECT COUNT(*) FROM login_logs ll WHERE 1=1";
    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND ll.user_id = ?";
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['ip_address'])) {
        $sql .= " AND ll.ip_address LIKE ?";
        $params[] = '%' . $filters['ip_address'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND ll.created_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND ll.created_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    if (isset($filters['success']) && $filters['success'] !== '') {
        $sql .= " AND ll.success = ?";
        $params[] = (int)$filters['success'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}
/**
 * Holt die Login-Logs basierend auf den angegebenen Filtern und Paginierung.
 * @param array $filters Filterkriterien
 * @param int|null $limit Anzahl der Einträge pro Seite
 * @param int|null $offset Offset für die Paginierung
 * @return array Liste der Login-Logs
 */
public static function getFilteredLoginLogs(array $filters = [], ?int $limit = null, ?int $offset = null): array
{
    $pdo = self::db_connect();

    $sql = "
        SELECT ll.*, u.username
        FROM login_logs ll
        LEFT JOIN users u ON ll.user_id = u.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND ll.user_id = ?";
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['ip_address'])) {
        $sql .= " AND ll.ip_address LIKE ?";
        $params[] = '%' . $filters['ip_address'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND ll.created_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND ll.created_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    if (isset($filters['success']) && $filters['success'] !== '') {
        $sql .= " AND ll.success = ?";
        $params[] = (int)$filters['success'];
    }

    $sql .= " ORDER BY ll.created_at DESC";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
/**
 * Zählt die Anzahl der Captcha-Logs basierend auf den angegebenen Filtern.
 * @param array $filters Filterkriterien
 * @return int Anzahl der Captcha-Logs
 */
public static function countFilteredCaptchaLogs(array $filters = []): int
{
    $pdo = self::db_connect();
    $sql = "SELECT COUNT(*) FROM captcha_log WHERE 1=1";
    $params = [];

    if ($filters['success'] !== '') {
        $sql .= " AND success = ?";
        $params[] = (int)$filters['success'];
    }
    if ($filters['action'] !== '') {
        $sql .= " AND action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
    }
    if ($filters['hostname'] !== '') {
        $sql .= " AND hostname LIKE ?";
        $params[] = '%' . $filters['hostname'] . '%';
    }
    if ($filters['score_min'] !== '') {
        $sql .= " AND score >= ?";
        $params[] = (float)$filters['score_min'];
    }
    if ($filters['score_max'] !== '') {
        $sql .= " AND score <= ?";
        $params[] = (float)$filters['score_max'];
    }
    if ($filters['from_date'] !== '') {
        $sql .= " AND created_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }
    if ($filters['to_date'] !== '') {
        $sql .= " AND created_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}
/**
 * Holt die Captcha-Logs basierend auf den angegebenen Filtern und Paginierung.
 * @param array $filters Filterkriterien
 * @param int|null $limit Anzahl der Einträge pro Seite
 * @param int|null $offset Offset für die Paginierung
 * @param bool $includeToken Soll das Token-Feld einbezogen werden?
 * @return array Liste der Captcha-Logs
 */
public static function getFilteredCaptchaLogs(array $filters = [], ?int $limit = null, ?int $offset = null, bool $includeToken = false): array
{
    $pdo = self::db_connect();

    $columns = $includeToken
        ? "token, success, score, action, hostname, error_reason, created_at"
        : "success, score, action, hostname, error_reason, created_at";

    $sql = "SELECT {$columns} FROM captcha_log WHERE 1=1";
    $params = [];

    if ($filters['success'] !== '') {
        $sql .= " AND success = ?";
        $params[] = (int)$filters['success'];
    }
    if ($filters['action'] !== '') {
        $sql .= " AND action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
    }
    if ($filters['hostname'] !== '') {
        $sql .= " AND hostname LIKE ?";
        $params[] = '%' . $filters['hostname'] . '%';
    }
    if ($filters['score_min'] !== '') {
        $sql .= " AND score >= ?";
        $params[] = (float)$filters['score_min'];
    }
    if ($filters['score_max'] !== '') {
        $sql .= " AND score <= ?";
        $params[] = (float)$filters['score_max'];
    }
    if ($filters['from_date'] !== '') {
        $sql .= " AND created_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }
    if ($filters['to_date'] !== '') {
        $sql .= " AND created_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $sql .= " ORDER BY created_at DESC";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
/*
 * Holt die Kontaktanfragen basierend auf den angegebenen Filtern.
 * @param array $filters Filterkriterien
 * @return array Liste der Kontaktanfragen
 */
public static function getFilteredContactRequests(array $filters = []): array
{
    $pdo = self::db_connect();
    $sql = "SELECT * FROM contact_requests WHERE 1=1";
    $params = [];

    if (!empty($filters['name'])) {
        $sql .= " AND name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }

    if (!empty($filters['email'])) {
        $sql .= " AND email LIKE ?";
        $params[] = '%' . $filters['email'] . '%';
    }

    if (!empty($filters['subject'])) {
        $sql .= " AND subject LIKE ?";
        $params[] = '%' . $filters['subject'] . '%';
    }

    if (!empty($filters['from'])) {
        $sql .= " AND created_at >= ?";
        $params[] = $filters['from'] . ' 00:00:00';
    }

    if (!empty($filters['to'])) {
        $sql .= " AND created_at <= ?";
        $params[] = $filters['to'] . ' 23:59:59';
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
/**
 * Zählt die Anzahl der Kontaktanfragen basierend auf den angegebenen Filtern.
 * @param array $filters Filterkriterien
 * @return int Anzahl der Kontaktanfragen
 */
public static function getFilteredPendingUploads(array $filters = [], ?int $limit = null, ?int $offset = null): array
{
    $pdo = self::db_connect();
    $sql = "
        SELECT u.*, m.title, c.name AS course_name, us.username
        FROM uploads u
        JOIN materials m ON u.material_id = m.id
        JOIN courses c ON m.course_id = c.id
        LEFT JOIN users us ON u.uploaded_by = us.id
        WHERE u.is_approved = 0 AND u.is_rejected = 0
    ";

    $params = [];

    if (!empty($filters['title'])) {
        $sql .= " AND m.title LIKE ?";
        $params[] = '%' . $filters['title'] . '%';
    }

    if (!empty($filters['filename'])) {
        $sql .= " AND u.stored_name LIKE ?";
        $params[] = '%' . $filters['filename'] . '%';
    }

    if (!empty($filters['username'])) {
        $sql .= " AND us.username LIKE ?";
        $params[] = '%' . $filters['username'] . '%';
    }

    if (!empty($filters['course_name'])) {
        $sql .= " AND c.name LIKE ?";
        $params[] = '%' . $filters['course_name'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND u.uploaded_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND u.uploaded_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $sql .= " ORDER BY u.uploaded_at DESC";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
/**
 * Zählt die Anzahl der ausstehenden Uploads, die noch nicht genehmigt oder abgelehnt wurden.
 * @param array $filters Filterkriterien
 * @return int Anzahl der ausstehenden Uploads
 */
public static function countFilteredPendingUploads(array $filters = []): int
{
    $pdo = self::db_connect();
    $sql = "
        SELECT COUNT(*)
        FROM uploads u
        JOIN materials m ON u.material_id = m.id
        JOIN courses c ON m.course_id = c.id
        LEFT JOIN users us ON u.uploaded_by = us.id
        WHERE u.is_approved = 0 AND u.is_rejected = 0
    ";

    $params = [];

    if (!empty($filters['title'])) {
        $sql .= " AND m.title LIKE ?";
        $params[] = '%' . $filters['title'] . '%';
    }

    if (!empty($filters['filename'])) {
        $sql .= " AND u.stored_name LIKE ?";
        $params[] = '%' . $filters['filename'] . '%';
    }

    if (!empty($filters['username'])) {
        $sql .= " AND us.username LIKE ?";
        $params[] = '%' . $filters['username'] . '%';
    }

    if (!empty($filters['course_name'])) {
        $sql .= " AND c.name LIKE ?";
        $params[] = '%' . $filters['course_name'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND u.uploaded_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND u.uploaded_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

/**
 * Holt die Kursempfehlungen basierend auf den angegebenen Filtern.
 * @param array $filters Filterkriterien
 * @return array Liste der Kursempfehlungen
 */
public static function getFilteredCourseSuggestions(array $filters = []): array
{
    $pdo = self::db_connect();
    $sql = "
        SELECT pcs.*, u.username, u.email
        FROM pending_course_suggestions pcs
        JOIN users u ON pcs.user_id = u.id
        WHERE pcs.is_approved IS NULL
    ";
    $params = [];

    if (!empty($filters['username'])) {
        $sql .= " AND u.username LIKE ?";
        $params[] = '%' . $filters['username'] . '%';
    }

    if (!empty($filters['course_name'])) {
        $sql .= " AND pcs.course_name LIKE ?";
        $params[] = '%' . $filters['course_name'] . '%';
    }

    if (!empty($filters['from_date'])) {
        $sql .= " AND pcs.suggested_at >= ?";
        $params[] = $filters['from_date'] . ' 00:00:00';
    }

    if (!empty($filters['to_date'])) {
        $sql .= " AND pcs.suggested_at <= ?";
        $params[] = $filters['to_date'] . ' 23:59:59';
    }

    $sql .= " ORDER BY pcs.suggested_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
/**
 * Zählt die Anzahl der Kursempfehlungen basierend auf den angegebenen Filtern.
 * @param array $filters Filterkriterien
 * @return int Anzahl der Kursempfehlungen
 */
public static function getFilteredLockedUsers(array $filters = []): array
{
    $pdo = self::db_connect();
    $sql = "
        SELECT u.id, u.username, u.email, us.failed_attempts
        FROM users u
        JOIN user_security us ON u.id = us.user_id
        WHERE us.account_locked = 1
    ";
    $params = [];

    if (!empty($filters['username'])) {
        $sql .= " AND u.username LIKE ?";
        $params[] = '%' . $filters['username'] . '%';
    }

    if ($filters['min_attempts'] !== '') {
        $sql .= " AND us.failed_attempts >= ?";
        $params[] = (int)$filters['min_attempts'];
    }

    if ($filters['max_attempts'] !== '') {
        $sql .= " AND us.failed_attempts <= ?";
        $params[] = (int)$filters['max_attempts'];
    }

    $sql .= " ORDER BY us.failed_attempts DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}


    /**
     * Speichert einen Password-Reset-Token.
     */
    public static function storePasswordResetToken(int $userId, string $token, int $expiresMinutes = 60): void
    {
        $sql = '
            INSERT INTO password_reset_tokens (user_id, reset_token, expires_at)
            VALUES (:uid, :token, DATE_ADD(NOW(), INTERVAL :exp MINUTE))
            ON DUPLICATE KEY UPDATE
                reset_token = VALUES(reset_token),
                expires_at = VALUES(expires_at)
        ';
        self::getLogger()->info('Store Password Reset Token', ['user_id' => $userId]);
        self::execute($sql, [
            ':uid'  => $userId,
            ':token'=> $token,
            ':exp'  => $expiresMinutes,
        ]);
    }

    /**
     * Holt den Benutzer anhand des Password-Reset-Tokens.
     */
    public static function fetchPasswordResetUser(string $token): ?array
    {
        $sql = '
            SELECT u.id, u.username, u.email, u.password_hash
            FROM password_reset_tokens pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.reset_token = :token AND pr.expires_at > NOW()
            LIMIT 1
        ';
        self::getLogger()->info('Fetch Password Reset User', ['token' => $token]);
        return self::fetchOne($sql, [':token' => $token]);
    }

    /**
     * Löscht einen Password-Reset-Token.
     */
    public static function deletePasswordResetToken(int $userId): int
    {
        $sql = 'DELETE FROM password_reset_tokens WHERE user_id = :id';
        self::getLogger()->info('Delete Password Reset Token', ['user_id' => $userId]);
        return self::execute($sql, [':id' => $userId], false);
    }

    /**
     * Aktualisiert das Passwort eines Benutzers.
     */
    public static function updatePassword(int $userId, string $passwordHash): int
    {
        $sql = 'UPDATE users SET password_hash = :pw WHERE id = :id';
        self::getLogger()->info('Update Password', ['user_id' => $userId]);
        return self::execute($sql, [':pw' => $passwordHash, ':id' => $userId], false);
    }

    /**
     * Aktualisiert die E-Mail-Adresse eines Benutzers.
     */
    public static function updateEmail(int $userId, string $email): int
    {
        $sql = 'UPDATE users SET email = :email WHERE id = :id';
        self::getLogger()->info('Update Email', ['user_id' => $userId]);
        return self::execute($sql, [':email' => $email, ':id' => $userId], false);
    }

    public static function fetchUserProfile(int $userId): ?array
    {
        $pdo = self::db_connect();
        $stmt = $pdo->prepare('SELECT * FROM profile WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return $profile ?: null;
    }

    public static function getOrCreateUserProfile(int $userId): array
    {
        $pdo = self::db_connect();

        $stmt = $pdo->prepare('SELECT * FROM profile WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$profile) {
            $stmt = $pdo->prepare('INSERT INTO profile (user_id) VALUES (:id)');
            $stmt->execute([':id' => $userId]);

            $stmt = $pdo->prepare('SELECT * FROM profile WHERE user_id = :id');
            $stmt->execute([':id' => $userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $profile ?: [];
    }

    public static function updateUserProfile(int $userId, array $fields): void
    {
        $pdo = self::db_connect();

        $set = [];
        $params = [':id' => $userId];

        foreach ($fields as $key => $value) {
            $set[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        if (empty($set)) {
            echo "⚠️ Kein Inhalt zum Speichern.<br>";
            exit;
        }

        $sql = 'UPDATE profile SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE user_id = :id';

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            self::getLogger()->error('Profil-Update fehlgeschlagen', [
                'error' => $e->getMessage(),
                'sql'   => $sql,
                'params'=> $params,
            ]);
            throw new RuntimeException('Fehler beim Speichern des Profils: ' . $e->getMessage());
        }
    }

    /**
     * Holt einen Benutzer anhand seiner ID.
     */
    public static function fetchUserById(int $userId): ?array
    {
        $sql = '
        SELECT
            u.id,
            u.username,
            u.email,
            u.password_hash,
            uv.is_verified,
            r.role_name AS role
        FROM users u
        JOIN user_verification uv ON u.id = uv.user_id
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id
        WHERE u.id = :userId
        LIMIT 1
            ';
        self::getLogger()->info('Fetch User By ID', ['user_id' => $userId]);
        return self::fetchOne($sql, [':userId' => $userId]);
    }

    /**
     * Löscht alle Einträge im Stundenplan eines Nutzers.
     */
    public static function deleteAllTimetableEntries(int $userId): void
    {
        $pdo = self::db_connect();

        $stmt = $pdo->prepare('DELETE FROM timetable WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Fügt einen Eintrag in den Stundenplan ein.
     */
    public static function insertTimetableEntry(
        int $userId,
        string $weekday,
        string $time,
        string $subject,
        string $room,
        int $slotIndex
    ): void {
        $pdo = self::db_connect();
        $subjectId = self::getOrCreateSubjectId($subject);
        $roomId    = self::getOrCreateRoomId($room);

        $stmt = $pdo->prepare(
            'INSERT INTO timetable (user_id, weekday, time, subject_id, room_id, slot_index)'
            . ' VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([$userId, $weekday, $time, $subjectId, $roomId, $slotIndex]);
    }

    /**
     * Holt den Stundenplan eines Nutzers für einen bestimmten Wochentag.
     */
    public static function getTimetableByDay(int $userId, string $weekday): array
    {
        $pdo = self::db_connect();

        $stmt = $pdo->prepare(
            'SELECT t.*, s.name AS subject, r.name AS room FROM timetable t LEFT JOIN subjects s ON t.subject_id = s.id LEFT JOIN rooms r ON t.room_id = r.id
             WHERE t.user_id = ? AND t.weekday = ?
             ORDER BY t.slot_index'
        );

        $stmt->execute([$userId, $weekday]);
        return $stmt->fetchAll();
    }

    /**
     * Holt oder erstellt eine subject_id.
     */
    private static function getOrCreateSubjectId(string $subject): ?int
    {
        if (trim($subject) === '') {
            return null;
        }

        $pdo = self::db_connect();
        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = ?');
        $stmt->execute([$subject]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (?)');
        $stmt->execute([$subject]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Holt oder erstellt eine room_id.
     */
    private static function getOrCreateRoomId(string $room): ?int
    {
        if (trim($room) === '') {
            return null;
        }

        $pdo = self::db_connect();
        $stmt = $pdo->prepare('SELECT id FROM rooms WHERE name = ?');
        $stmt->execute([$room]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $stmt = $pdo->prepare('INSERT INTO rooms (name) VALUES (?)');
        $stmt->execute([$room]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Gibt alle Social-Media-Einträge eines Nutzers zurück.
     *
     * @param int $userId Die ID des Nutzers
     * @return array[] Liste aus [platform => string, username => string]
     */
    public static function getUserSocialMedia(int $userId): array
    {
        $sql = 'SELECT platform, username FROM social_media WHERE user_id = :uid';
        return self::execute($sql, [':uid' => $userId], true);
    }

    /**
     * Speichert einen Social-Media-Eintrag. Existiert bereits einer mit gleicher
     * Plattform für den Nutzer, wird dieser aktualisiert.
     *
     * @param int    $userId   Die ID des Nutzers
     * @param string $platform Die Plattform
     * @param string $username Der Benutzername
     */
    public static function saveUserSocialMedia(int $userId, string $platform, string $username): void
    {
        $pdo = self::db_connect();
        $stmt = $pdo->prepare('SELECT id FROM social_media WHERE user_id = :uid AND platform = :platform');
        $stmt->execute([':uid' => $userId, ':platform' => $platform]);
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            if ($username === '') {
                $del = $pdo->prepare('DELETE FROM social_media WHERE id = :id');
                $del->execute([':id' => $existingId]);
            } else {
                $update = $pdo->prepare('UPDATE social_media SET username = :uname WHERE id = :id');
                $update->execute([':uname' => $username, ':id' => $existingId]);
            }
        } elseif ($username !== '') {
            $insert = $pdo->prepare('INSERT INTO social_media (user_id, platform, username) VALUES (:uid, :platform, :uname)');
            $insert->execute([':uid' => $userId, ':platform' => $platform, ':uname' => $username]);
        }
    }
}
