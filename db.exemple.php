<?php
define('DB_HOST', 'mysql-toncompte.alwaysdata.net');
define('DB_NAME', 'toncompte_portfolio');
define('DB_USER', 'toncompte_user');
define('DB_PASS', '');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Erreur de connexion. Veuillez réessayer plus tard.');
}