<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_path'     => '/FellahDz/',
        'use_strict_mode' => true
    ]);
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$email    = trim($input['email']    ?? '');
$password = trim($input['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Email and password required']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=fellah_dz;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || ($password !== $user['password_hash'] && !password_verify($password, $user['password_hash']))) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    exit;
}

session_unset();
$_SESSION['user_id']    = $user['id'];
$_SESSION['email']      = $user['email'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name']  = $user['last_name'];
$_SESSION['role']       = $user['role'];
session_regenerate_id(true);

$redirectMap = [
    'admin'  => 'user-management.html',
    'farmer' => 'homepage.html',
    'worker' => 'worker-dashboard.html',
];
$redirect = $redirectMap[$user['role']] ?? 'home.html';

echo json_encode([
    'success'  => true,
    'redirect' => $redirect,
    'user'     => [
        'id'         => $user['id'],
        'email'      => $user['email'],
        'first_name' => $user['first_name'],
        'last_name'  => $user['last_name'],
        'role'       => $user['role'],
        'initials'   => strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1))
    ]
]);
?>