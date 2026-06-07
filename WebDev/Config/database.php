<?php
/**
 * KONFIGURIMI I DATABAZËS (Docker Compatible)
 * ===========================================
 * Përdor environment variables nga Docker ose vlerat default.
 */

// Merr konfigurimet nga environment variables (Docker) ose përdor default
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'web_platform');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * KLASA DATABASE
 * ===============
 * Kjo klasë menaxhon lidhjen me MySQL duke përdorur PDO.
 *
 * SHPJEGIM për fillestarët:
 * - PDO (PHP Data Objects) është mënyra moderne dhe e sigurt për tu lidhur me databaza
 * - Singleton Pattern: Siguron që të kemi vetëm NJË lidhje me databazën
 * - Prepared Statements: Mbron nga SQL Injection (sulme hakerash)
 */
class Database
{
    // Variabël private që ruan instancën e vetme
    private static $instance = null;

    // Objekti PDO për lidhjen
    private $pdo;

    /**
     * KONSTRUKTORI (private për Singleton)
     * Krijon lidhjen me databazën
     */
    private function __construct()
    {
        $maxRetries = 10;
        $retryDelay = 3; // sekonda

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // DSN (Data Source Name) - stringa e lidhjes
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

                // Opsione për PDO
                $options = [
                    // Hidh gabime si Exceptions (më e lehtë për debugging)
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                    // Kthen rezultatet si array asociativ
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Përdor prepared statements të vërtetë (më sigurt)
                    PDO::ATTR_EMULATE_PREPARES => false,

                    // Timeout për lidhjen
                    PDO::ATTR_TIMEOUT => 5,
                ];

                // Krijo lidhjen
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

                // Lidhja u krye me sukses
                return;

            } catch (PDOException $e) {
                // Në Docker, MySQL mund të marrë kohë të niset
                if ($attempt < $maxRetries) {
                    error_log("Tentativa $attempt/$maxRetries: Duke pritur MySQL... ({$e->getMessage()})");
                    sleep($retryDelay);
                    continue;
                }

                // Në rast gabimi final, shfaq mesazh
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    die("Gabim në lidhjen me databazën: " . $e->getMessage());
                } else {
                    die("Gabim në server. Ju lutemi provoni më vonë.");
                }
            }
        }
    }

    /**
     * Merr instancën e vetme të Database (Singleton)
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Merr objektin PDO për query direkte
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Ekzekuton një query me parametra (SIGURT nga SQL Injection)
     *
     * SHEMBULL:
     * $db->query("SELECT * FROM users WHERE email = ?", [$email]);
     *
     * @param string $sql - Query SQL me placeholder (?)
     * @param array $params - Vlerat për placeholders
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Merr një rresht të vetëm
     *
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function fetchOne(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Merr të gjitha rreshtat
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Shton një rresht të ri dhe kthen ID-në
     *
     * @param string $table - Emri i tabelës
     * @param array $data - Array asociativ ['kolona' => 'vlera']
     * @return int - ID e rreshtit të ri
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Përditëson rreshta
     *
     * @param string $table
     * @param array $data - Të dhënat për përditësim
     * @param string $where - Kushti WHERE
     * @param array $whereParams - Parametrat për WHERE
     * @return int - Numri i rreshtave të prekur
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);

        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Fshin rreshta
     *
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Numëron rreshtat
     *
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function count(string $table, string $where = '1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetchOne($sql, $params);
        return (int) $result['count'];
    }

    /**
     * Fillon një transaksion
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Konfirmon transaksionin
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Anulon transaksionin
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Ndalon klonimin (për Singleton)
     */
    private function __clone() {}

    /**
     * Ndalon unserialize (për Singleton)
     */
    public function __wakeup()
    {
        throw new Exception("Nuk mund të deserialize Singleton");
    }
}
