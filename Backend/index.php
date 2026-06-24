<?php
// backend/index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config/db.php';
require_once 'helpers/jwt.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/PropiedadController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/FavoritoController.php';

// Leer la URL directamente para que funcione con php -S
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = isset($_GET['url']) ? $_GET['url'] : ltrim($uri, '/');
$url = rtrim($url, '/');
$urlSegmentos = explode('/', $url);
$metodo = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents("php://input"), true) ?? [];

// Helper para extraer token Bearer
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$tokenData = false;
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $tokenData = JWT::verificar($matches[1]);
}

// ── SISTEMA DE RUTAS EMULADO ──
if ($urlSegmentos[0] === 'api') {
    array_shift($urlSegmentos); // Quitar prefijo 'api'
    
    // Rutas: /api/auth
    if ($urlSegmentos[0] === 'auth') {
        if ($urlSegmentos[1] === 'registro' && $metodo === 'POST') AuthController::registro($pdo, $body);
        if ($urlSegmentos[1] === 'login' && $metodo === 'POST') AuthController::login($pdo, $body);
    }
    
    // Rutas: /api/admin
    if ($urlSegmentos[0] === 'admin') {
        if (!$tokenData || $tokenData['rol'] !== 'admin') { http_response_code(403); echo json_encode(["mensaje" => "No autorizado"]); exit; }
        if ($urlSegmentos[1] === 'stats' && $metodo === 'GET') AdminController::getStats($pdo);
        if ($urlSegmentos[1] === 'usuarios' && !isset($urlSegmentos[2]) && $metodo === 'GET') AdminController::getUsuarios($pdo);
        if ($urlSegmentos[1] === 'usuarios' && isset($urlSegmentos[2]) && $urlSegmentos[3] === 'rol' && $metodo === 'PUT') AdminController::cambiarRol($pdo, $urlSegmentos[2], $body);
        if ($urlSegmentos[1] === 'usuarios' && isset($urlSegmentos[2]) && !isset($urlSegmentos[3]) && $metodo === 'DELETE') AdminController::eliminarUsuario($pdo, $urlSegmentos[2]);
        if ($urlSegmentos[1] === 'propiedades' && !isset($urlSegmentos[2]) && $metodo === 'GET') AdminController::getPropiedades($pdo);
        if ($urlSegmentos[1] === 'propiedades' && isset($urlSegmentos[2]) && $urlSegmentos[3] === 'estado' && $metodo === 'PUT') AdminController::cambiarEstado($pdo, $urlSegmentos[2], $body);
    }

    // Rutas: /api/propiedades
    if ($urlSegmentos[0] === 'propiedades') {
        if (!isset($urlSegmentos[1]) && $metodo === 'GET') PropiedadController::listar($pdo, $_GET);
        if ($urlSegmentos[1] === 'mis-propiedades' && $metodo === 'GET') PropiedadController::misPropiedades($pdo, $tokenData);
        if (!isset($urlSegmentos[1]) && $metodo === 'POST') PropiedadController::crear($pdo, $body, $tokenData);
        if (isset($urlSegmentos[1]) && is_numeric($urlSegmentos[1]) && $metodo === 'GET') PropiedadController::obtenerPorId($pdo, $urlSegmentos[1]);
    }

    // Rutas: /api/favoritos
    if ($urlSegmentos[0] === 'favoritos' && isset($urlSegmentos[1])) {
        if ($metodo === 'POST') FavoritoController::agregar($pdo, $urlSegmentos[1], $tokenData);
        if ($metodo === 'DELETE') FavoritoController::eliminar($pdo, $urlSegmentos[1], $tokenData);
    }
} else {
    http_response_code(404);
    echo json_encode(["mensaje" => "Endpoint no encontrado"]);
}