<?php
$databaseUrl = "MySQL.root:iWoBGfGiJZQUbjGoExNLVMUymoNJNwvl@mysql.railway.internal:3306/railway";
$parts = parse_url($databaseUrl);

$host = $parts['host'];
$port = $parts['port'];
$user = $parts['user'];
$pass = $parts['pass'];
$db   = ltrim($parts['path'], '/');

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL via Railway!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
