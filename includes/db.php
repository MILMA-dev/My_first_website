<?php
$host = 'localhost';
$db   = 'microsaas';
$user = 'root';
$pass = ''; // MySQL Workbench usually uses root with or without password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // In the sandbox environment we don't have a real MySQL server running
     // But we provide the code for the user to use in their environment
     // To keep the agent working, we will fallback to SQLite IF we are in the sandbox
     // and mysql is not available
     $sqlite_path = __DIR__ . '/../database.sqlite';
     $pdo = new PDO("sqlite:" . $sqlite_path);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
?>
