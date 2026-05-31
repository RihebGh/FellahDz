<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_clean();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/database.php';
$pdo = (new Database())->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data   = json_decode(file_get_contents('php://input'), true) ?? [];

function apiError($code, $msg) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM seeds WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch() ?: ['error' => 'Not found']);
        } else {
            echo json_encode($pdo->query("SELECT * FROM seeds ORDER BY type, name")->fetchAll());
        }
        break;

    case 'POST':
        foreach (['name','type','quantity','expiration_date','location'] as $f)
            if (empty($data[$f])) apiError(400, 'Missing required fields');

        $stmt = $pdo->prepare("INSERT INTO seeds (name,type,quantity,expiration_date,location) VALUES (?,?,?,?,?)");
        $stmt->execute([$data['name'],$data['type'],$data['quantity'],$data['expiration_date'],$data['location']]);

        $stmt = $pdo->prepare("SELECT * FROM seeds WHERE id = ?");
        $stmt->execute([$pdo->lastInsertId()]);
        http_response_code(201);
        echo json_encode($stmt->fetch());
        break;

    case 'PUT':
        if (empty($data['id'])) apiError(400, 'ID required');

        $pdo->prepare("UPDATE seeds SET name=?,type=?,quantity=?,expiration_date=?,location=? WHERE id=?")
            ->execute([$data['name'],$data['type'],$data['quantity'],$data['expiration_date'],$data['location'],$data['id']]);

        $stmt = $pdo->prepare("SELECT * FROM seeds WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode($stmt->fetch());
        break;

    case 'DELETE':
        $id = $data['id'] ?? $_GET['id'] ?? null;
        if (!$id) apiError(400, 'ID required');

        $pdo->prepare("DELETE FROM seeds WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Seed deleted']);
        break;

    default:
        apiError(405, 'Method not allowed');
}
?>