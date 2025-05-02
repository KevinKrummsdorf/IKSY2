<?php

declare(strict_types=1);


use Dotenv\Dotenv;

// .env-Datei laden
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class DbFunctions
{
    // Datenbank-Konfiguration als Klassenkonstanten
private const DB_HOST     = $_ENV['DB_HOST'];
private const DB_USER     = $_ENV['DB_USER'];
private const DB_PASSWORD = $_ENV['DB_PASSWORD'];
private const DB_NAME     = $_ENV['DB_NAME'];

    
    
    // Fehler-Logging-Funktion mit Logrotation
    public function log_error(string $message): void
    {
        $logFile = __DIR__ . '/../logs/db_error.log';
        // … Rotation …
    
        // wieder einkommentieren:
        error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, $logFile);
    }
    
    
    // Datenbankverbindung herstellen
    public static function db_connect(): PDO
    {
        // 1) Umgebungsvariablen mit Fallbacks
        $host = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?? 'localhost';
        $port = $_ENV['DB_PORT']     ?? getenv('DB_PORT')     ?? '5432';
        $db   = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'studyhub';
        $user = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'admin';
        $pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? 'admin';
    
        // 2) DSN für PostgreSQL
        $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
    
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    
        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Schreibe den Fehler ins log-File
            (new self())->log_error('DB-Verbindung fehlgeschlagen: ' . $e->getMessage());
    
            // HTTP-500 + JSON-Fehler
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => defined('DEBUG') && DEBUG
                    ? 'DB-Fehler: ' . $e->getMessage()
                    : 'Interner Serverfehler. Bitte später erneut versuchen.'
            ]);
    
            // Exception weiterwerfen, damit register.php sie im Try-Catch fängt
            throw $e;
        }
    }
    
    
    
    
    
    

    /**
     * Escaped einen String für eine Query.
     */
    public static function escape(mysqli $link, string $str): string
    {
        return $link->real_escape_string($str);
    }

    /**
     * Führt ein einfaches (unparametriertes) Query aus und liefert das Resultset oder wirft.
     */
    public static function executeQuery(mysqli $link, string $query): mysqli_result
    {
        $result = $link->query($query);
        if ($result === false) {
            throw new \RuntimeException(
                'Query-Fehler: ' . $link->error
            );
        }
        return $result;
    }

    /**
     * Holt alle Zeilen als assoziatives Array oder null, falls keine Zeilen.
     */
    public static function getAssociativeResultArray(mysqli $link, string $query): ?array
    {
        $result     = self::executeQuery($link, $query);
        $rowCount   = $result->num_rows;
        if ($rowCount === 0) {
            $result->free();
            return null;
        }

        $resultArray = [];
        while ($row = $result->fetch_assoc()) {
            $resultArray[] = $row;
        }
        $result->free();
        return $resultArray;
    }

    /**
     * Holt das erste Ergebnis als assoziatives Array oder null.
     */
    public static function getHashFromFirstRow(mysqli $link, string $query): ?array
    {
        $rows = self::getAssociativeResultArray($link, $query);
        return $rows[0] ?? null;
    }

    /**
     * Holt ein Key=>Value-Paar-Array aus zwei Spalten (erste Spalte = Key, zweite = Value).
     */
    public static function getHash(mysqli $link, string $query): ?array
    {
        $result   = self::executeQuery($link, $query);
        $rowCount = $result->num_rows;
        if ($rowCount === 0) {
            $result->free();
            return null;
        }

        $fieldList = [];
        while ($row = $result->fetch_row()) {
            $fieldList[(string)$row[0]] = $row[1];
        }
        $result->free();
        return $fieldList;
    }

    /**
     * Gibt die erste Feldspalte des ersten Datensatzes zurück, oder null.
     */
    public static function getFirstFieldOfResult(mysqli $link, string $query): mixed
    {
        $result = self::executeQuery($link, $query);
        if ($result->num_rows === 0) {
            $result->free();
            return null;
        }
        $row = $result->fetch_row();
        $result->free();
        return $row[0];
    }

    /**
     * Führt ein Prepared Statement aus.
     *
     * @param bool $expectResult Wenn true, wird ein Array von Zeilen zurückgegeben, sonst die Anzahl
     *                           der betroffenen Zeilen (INSERT/UPDATE).
     * @return mixed
     * @throws RuntimeException
     */
    public static function executePreparedQuery(
        mysqli $link,
        string $query,
        array $params = [],
        bool $expectResult = false
    ): mixed {
        $stmt = $link->prepare($query);
        if ($stmt === false) {
            throw new \RuntimeException(
                'Prepare-Fehler: ' . $link->error
            );
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                $types .= match (gettype($param)) {
                    'integer' => 'i',
                    'double'  => 'd',
                    'string'  => 's',
                    default   => 'b',
                };
            }
            if (! $stmt->bind_param($types, ...$params)) {
                $stmt->close();
                throw new \RuntimeException(
                    'Bind-Param-Fehler: ' . $stmt->error
                );
            }
        }

        if (! $stmt->execute()) {
            $stmt->close();
            throw new \RuntimeException(
                'Execute-Fehler: ' . $stmt->error
            );
        }

        if ($expectResult) {
            $result = $stmt->get_result();
            if ($result === false) {
                $stmt->close();
                throw new \RuntimeException(
                    'Fetch-Result-Fehler: ' . $stmt->error
                );
            }
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->free();
            $stmt->close();
            return $data;
        }

        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    /** Zusätzliche Helfer für Fehlernummer und -text */
    public static function getErrorNumber(mysqli $link): int
    {
        return $link->errno;
    }

    public static function getErrorText(mysqli $link): string
    {
        return $link->error;
    }
}
