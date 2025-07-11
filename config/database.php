<?php
/**
 * Database Configuration for ifiti Real Estate
 */
// Get the DATABASE_URL from the environment
$databaseUrl = getenv("mysql://root:iWoBGfGiJZQUbjGoExNLVMUymoNJNwvl@hopper.proxy.rlwy.net:32168/railway");

// Parse the URL
$parts = parse_url($databaseUrl);

$host = $parts['mysql.railway.internal'];
$user = $parts['root'];
$pass = $parts['iWoBGfGiJZQUbjGoExNLVMUymoNJNwvl'];
$db   = ltrim($parts['railway'], '/');


class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}
?>
