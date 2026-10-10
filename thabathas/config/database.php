<?php
// Conexión PDO a la base de datos local del prototipo. Ajustar estos valores si el entorno cambia.
$host = 'localhost';
$db = 'thabathas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('No fue posible conectar con la base de datos. Verifique XAMPP y la configuración de MySQL.');
}
