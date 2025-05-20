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
                'query' => $query,
                'params' => $params,
                'errorInfo' => $stmt->errorInfo(),
            ]);
            throw new RuntimeException('Fehler beim Ausführen des Statements.');
        }

        return $expectResult ? $stmt->fetchAll() : $stmt->rowCount();
    }

    /**
     * Holt die erste Zeile als assoziatives Array oder null.
     */
    public static function fetchOne(string $query, array $params = []): ?array
    {
        $pdo = self::db_connect();
        $stmt = $pdo->prepare($query);
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
        $stmt = $pdo->prepare($query);
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
            SELECT u.id, u.username, u.is_verified
            FROM verification_tokens vt
            JOIN users u ON u.id = vt.user_id
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
        $sql = 'UPDATE users SET is_verified = TRUE WHERE id = :id';
        self::getLogger()->info('Verify User', [
            'user_id' => $userId,
        ]);
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
            INSERT INTO users (username, email, password_hash, is_verified)
            VALUES (:u, :e, :p, 0)
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

        return (int)self::lastInsertId();
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
                u.id, u.username, u.password_hash, u.is_verified,
                r.role_name AS role
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.username = :identUser OR u.email = :identEmail
            LIMIT 1
        ';
        self::getLogger()->info('Fetch User by Identifier', [
            'input' => $input,
        ]);
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
        $sql = '
            UPDATE users 
            SET twofa_secret = :secret, is_twofa_enabled = 1
            WHERE username = :username
        ';
        self::getLogger()->info('Store 2FA Secret', [
            'username' => $username
        ]);        
        self::execute($sql, [
            ':secret'   => $encryptedSecret,
            ':username' => $username,
        ]);
    }

    /**
     * Ruft das verschlüsselte 2FA-Secret eines Benutzers ab.
     */
    public static function getTwoFASecret(string $username): ?string
    {
        $sql = '
            SELECT twofa_secret 
            FROM users 
            WHERE username = :username AND is_twofa_enabled = 1
        ';
        self::getLogger()->info('Fetch 2FA Secret', [
            'username' => $username,
        ]);
        return self::fetchValue($sql, [':username' => $username]);
    }

    /**
     * Prüft, ob für einen Benutzer 2FA aktiviert ist.
     */
    public static function isTwoFAEnabled(string $username): bool
    {
        $sql = '
            SELECT is_twofa_enabled 
            FROM users 
            WHERE username = :username
        ';
        self::getLogger()->info('Check 2FA Enabled', [
            'username' => $username,
        ]);
        return (bool) self::fetchValue($sql, [':username' => $username]);
    }

    /**
     * Deaktiviert 2FA für einen Benutzer.
     */
    public static function disableTwoFA(string $username): void
    {
        $sql = '
            UPDATE users 
            SET twofa_secret = NULL, is_twofa_enabled = 0
            WHERE username = :username
        ';
        self::getLogger()->info('Disable 2FA', [
            'username' => $username,
        ]);
        self::execute($sql, [':username' => $username]);
    }
}

