<?php
// backend/config/db.php
$host = 'localhost';
$db   = 'estatearch_db';
$user = 'root'; // Ajusta tus credenciales
$pass = ''; 
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["mensaje" => "Error de conexión: " . $e->getMessage()]);
    exit;
}