<?php
$databaseUrl = getenv("DATABASE_URL");
$parts = parse_url($databaseUrl);

$host = $parts['host'];
$port = $parts['port'];
$user = $parts['user'];
$pass = $parts['pass'];
$db   = ltrim($parts['path'], '/');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL on Railway!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
