<?php
$databaseUrl = getenv("mysql://root:iWoBGfGiJZQUbjGoExNLVMUymoNJNwvl@hopper.proxy.rlwy.net:32168/railway");
$parts = parse_url($databaseUrl);

$host = $parts['MYSQLHOST'];
$port = $parts['MYSQLPORT'];
$user = $parts['MYSQLUSER'];
$pass = $parts['MYSQLPASSWORD'];
$db   = ltrim($parts['path'], '/');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL on Railway!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
