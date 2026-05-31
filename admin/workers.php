<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
    $pdo = new PDO("mysql:host=localhost;dbname=fellah_dz;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "error" => "DB connection failed"]));
}

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true) ?? [];

// Helper: sync the same changes to the users table
function syncUser($pdo, $email, $fields, $params) {
    $set = implode(', ', array_map(fn($f) => "$f = :$f", $fields));
    $stmt = $pdo->prepare("UPDATE users SET $set WHERE email = :email");
    $stmt->execute(array_merge($params, [':email' => $email]));
}

switch ($method) {

    case 'GET':
        $workers = $pdo->query("SELECT * FROM workers ORDER BY id ASC")->fetchAll();
        echo json_encode(["success" => true, "workers" => $workers]);
        break;

    case 'POST':
        $hash = password_hash($input['password'], PASSWORD_BCRYPT);
        $role = $input['role']   ?? 'worker';
        $status = $input['status'] ?? 'active';

        $pdo->prepare("INSERT INTO workers (first_name, last_name, email, password_hash, phone, role, status, created_at)
                       VALUES (:first_name, :last_name, :email, :hash, :phone, :role, :status, NOW())")
            ->execute([':first_name' => $input['first_name'], ':last_name' => $input['last_name'],
                       ':email' => $input['email'], ':hash' => $hash,
                       ':phone' => $input['phone'], ':role' => $role, ':status' => $status]);

        $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status)
                       VALUES (:email, :hash, :first_name, :last_name, :phone, :role, :status)
                       ON DUPLICATE KEY UPDATE password_hash = :hash")
            ->execute([':email' => $input['email'], ':hash' => $hash,
                       ':first_name' => $input['first_name'], ':last_name' => $input['last_name'],
                       ':phone' => $input['phone'], ':role' => $role, ':status' => $status]);

        echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        $hasPassword = !empty($input['password']);
        $hash = $hasPassword ? password_hash($input['password'], PASSWORD_BCRYPT) : null;
        $passField = $hasPassword ? "password_hash = :hash," : "";

        $pdo->prepare("UPDATE workers SET first_name=:first_name, last_name=:last_name,
                       email=:email, $passField phone=:phone, status=:status WHERE id=:id")
            ->execute(array_filter([
                ':id' => $input['id'], ':first_name' => $input['first_name'],
                ':last_name' => $input['last_name'], ':email' => $input['email'],
                ':phone' => $input['phone'], ':status' => $input['status'],
                ':hash' => $hash,
            ]));

        // Get current email to update users table
        $email = $pdo->prepare("SELECT email FROM workers WHERE id = ?");
        $email->execute([$input['id']]);
        $currentEmail = $email->fetchColumn();

        $pdo->prepare("UPDATE users SET first_name=:first_name, last_name=:last_name,
                       email=:email, $passField phone=:phone, status=:status WHERE email=:current_email")
            ->execute(array_filter([
                ':first_name' => $input['first_name'], ':last_name' => $input['last_name'],
                ':email' => $input['email'], ':phone' => $input['phone'],
                ':status' => $input['status'], ':current_email' => $currentEmail,
                ':hash' => $hash,
            ]));

        echo json_encode(["success" => true]);
        break;

    case 'DELETE':
        $stmt = $pdo->prepare("SELECT email FROM workers WHERE id = ?");
        $stmt->execute([$input['id']]);
        $email = $stmt->fetchColumn();

        if ($email) $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
        $pdo->prepare("DELETE FROM workers WHERE id = ?")->execute([$input['id']]);

        echo json_encode(["success" => true]);
        break;

    default:
        echo json_encode(["success" => false, "error" => "Method not allowed"]);
}