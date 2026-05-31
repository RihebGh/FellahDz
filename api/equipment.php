<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_clean();
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $result = $stmt->fetch();
            echo json_encode($result ?: ["error" => "Not found"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM equipment ORDER BY type, name");
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name']) || empty($data['type']) || empty($data['location']) || empty($data['maintenance_date'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO equipment (name, type, status, maintenance_date, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['type'], $data['status'] ?? 'Good', $data['maintenance_date'], $data['location']]);
        $newId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$newId]);
        http_response_code(201);
        echo json_encode($stmt->fetch());
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) { http_response_code(400); echo json_encode(["error" => "ID required"]); exit; }
        $stmt = $pdo->prepare("UPDATE equipment SET name=?, type=?, status=?, maintenance_date=?, location=? WHERE id=?");
        $stmt->execute([$data['name'], $data['type'], $data['status'], $data['maintenance_date'], $data['location'], $data['id']]);
        $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode($stmt->fetch());
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(["error" => "ID required"]); exit; }
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true, "message" => "Equipment deleted"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
}