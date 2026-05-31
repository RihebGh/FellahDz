<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
class Database {
    private $host     = "localhost";
    private $db_name  = "fellah_dz";
    private $username = "root";
    private $password = "";

    public function getConnection() {
        try {
            $conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("set names utf8");
            return $conn;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Connection error: " . $e->getMessage()]);
            exit;
        }
    }
}

function checkRole($requiredRole) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
    if ($_SESSION['role'] !== $requiredRole && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden - Insufficient permissions"]);
        exit;
    }
}
?>