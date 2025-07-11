<?php
$databaseUrl = getenv("mysql://root:iWoBGfGiJZQUbjGoExNLVMUymoNJNwvl@mysql.railway.internal:3306/railway");
$parts = parse_url($databaseUrl);

$host = $parts['mysql.railway.internal'];
$port = $parts['3306'];
$user = $parts['root'];
$pass = $parts['iWoBGfGiJZQUbjGoExNLVMUymoNJNwvl'];
$db   = ltrim($parts['path'], '/');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL on Railway!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
