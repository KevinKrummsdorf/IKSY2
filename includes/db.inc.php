<?php

declare(strict_types=1);
    require_once __DIR__ . '/../includes/logger.inc.php';

class DbFunctions
{
    private static ?PDO $pdo = null;
    private static ?MonologLoggerAdapter $log = null;

    private static function getLogger(): MonologLoggerAdapter
    {
        if (self::$log === null) {
            $monolog = getLogger('db');
            self::$log = new MonologLoggerAdapter($monolog);
        }
        return self::$log;
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
                'message' => defined('DEBUG') && DEBUG
                    ? 'DB-Fehler: ' . $e->getMessage()
                    : 'Interner Serverfehler. Bitte später erneut versuchen.'
            ]);
            throw $e;
        }
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
    public static function insertUploadLog(int $userId, string $storedName): int
    {
        $sql = '
            INSERT INTO upload_logs
               (user_id, stored_name)
            VALUES
               (:user_id, :stored_name)
        ';
        self::getLogger()->info('INSERT Upload Log', [
            'user_id'     => $userId,
            'stored_name' => $storedName,
        ]);
        return self::execute($sql, [
            ':user_id'     => $userId,
            ':stored_name' => $storedName,
        ], false);
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
            u.id, u.username, u.password_hash,
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

public static function countLoginLogs(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM login_logs');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

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

public static function countCaptchaLogs(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM captcha_log');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

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

public static function countContactRequests(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM contact_requests');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

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

public static function countUploadLogs(): int
{
    $stmt = self::db_connect()->prepare('SELECT COUNT(*) FROM upload_logs');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

public static function getUploadLogsPage(int $limit, int $offset, bool $isAdmin, bool $isMod): array
{
    if (!$isAdmin && !$isMod) {
        return [];
    }

    $stmt = self::db_connect()->prepare('
        SELECT id, user_id, stored_name, uploaded_at
        FROM upload_logs
        ORDER BY uploaded_at DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

public static function getUserById(int $userId): ?array
{
    $sql = 'SELECT username, email FROM users WHERE id = :id';
    $stmt = self::db_connect()->prepare($sql);
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}





}