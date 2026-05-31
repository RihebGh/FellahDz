<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        checkRole('admin');
        $query = isset($_GET['id'])
            ? "SELECT id, email, first_name, last_name, phone, role, status, created_at FROM users WHERE id = :id"
            : "SELECT id, email, first_name, last_name, phone, role, status, created_at FROM users ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        if (isset($_GET['id'])) $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        checkRole('admin');
        $data = json_decode(file_get_contents('php://input'));

        if (empty($data->email) || empty($data->password) || empty($data->first_name) || empty($data->last_name)) {
            http_response_code(400); echo json_encode(['error' => 'Missing required fields']); exit;
        }
        if (!preg_match('/@fellahdz\.com$/', $data->email)) {
            http_response_code(400); echo json_encode(['error' => 'Email must be in format: name@fellahdz.com']); exit;
        }

        $check = $db->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute([':email' => $data->email]);
        if ($check->rowCount() > 0) {
            http_response_code(409); echo json_encode(['error' => 'Email already exists']); exit;
        }

        $hash   = password_hash($data->password, PASSWORD_BCRYPT);
        $role   = $data->role   ?? 'worker';
        $status = $data->status ?? 'active';
        $phone  = $data->phone  ?? '';

        $db->prepare("INSERT INTO users (email, password_hash, first_name, last_name, phone, role, status)
                      VALUES (:email, :hash, :first_name, :last_name, :phone, :role, :status)")
           ->execute([':email' => $data->email, ':hash' => $hash, ':first_name' => $data->first_name,
                      ':last_name' => $data->last_name, ':phone' => $phone, ':role' => $role, ':status' => $status]);

        $userId = $db->lastInsertId();

        // Sync to workers table if not already there
        $workerCheck = $db->prepare('SELECT id FROM workers WHERE email = :email');
        $workerCheck->execute([':email' => $data->email]);
        if ($workerCheck->rowCount() === 0) {
            $db->prepare("INSERT INTO workers (first_name, last_name, email, phone, role, status)
                          VALUES (:first_name, :last_name, :email, :phone, :role, :status)")
               ->execute([':first_name' => $data->first_name, ':last_name' => $data->last_name,
                          ':email' => $data->email, ':phone' => $phone, ':role' => $role, ':status' => $status]);
        }

        echo json_encode(['success' => true, 'message' => 'User created successfully', 'id' => $userId]);
        break;

    case 'PUT':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit;
        }

        $data = json_decode(file_get_contents('php://input'));
        if (empty($data->id)) {
            http_response_code(400); echo json_encode(['error' => 'Missing user id']); exit;
        }

        $targetId = (int) $data->id;
        if ($_SESSION['role'] !== 'admin' && (int) $_SESSION['user_id'] !== $targetId) {
            http_response_code(403); echo json_encode(['error' => 'You can only update your own account']); exit;
        }

        // Sub-action 1: Status change
        if (isset($data->status)) {
            $newStatus = in_array($data->status, ['active', 'blocked']) ? $data->status : 'blocked';
            $stmt = $db->prepare('UPDATE users SET status = :status WHERE id = :id');
            $stmt->execute([':status' => $newStatus, ':id' => $targetId]);
            if ($stmt->rowCount() === 0) {
                http_response_code(404); echo json_encode(['error' => 'User not found or status unchanged']); exit;
            }
            $db->prepare("UPDATE workers SET status = :status
                          WHERE email = (SELECT email FROM (SELECT email FROM users WHERE id = :id) AS u)")
               ->execute([':status' => $newStatus, ':id' => $targetId]);
            echo json_encode(['success' => true, 'message' => 'Account status updated', 'status' => $newStatus]);
            exit;
        }

        // Sub-action 2: Change password
        if (isset($data->password)) {
            if (strlen($data->password) < 8) {
                http_response_code(400); echo json_encode(['error' => 'New password must be at least 8 characters']); exit;
            }
            $hash = password_hash($data->password, PASSWORD_BCRYPT);
            $db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')->execute([':hash' => $hash, ':id' => $targetId]);
            $db->prepare("UPDATE workers SET password_hash = :hash
                          WHERE email = (SELECT email FROM (SELECT email FROM users WHERE id = :id) AS u)")
               ->execute([':hash' => $hash, ':id' => $targetId]);
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
            exit;
        }

        // Sub-action 3: Update personal info
        if (!empty($data->email) && !preg_match('/@fellahdz\.com$/', $data->email)) {
            http_response_code(400); echo json_encode(['error' => 'Email must be in format: name@fellahdz.com']); exit;
        }

        $fields = []; $params = [':id' => $targetId];
        if (!empty($data->email))      { $fields[] = 'email = :email';           $params[':email']      = $data->email; }
        if (!empty($data->first_name)) { $fields[] = 'first_name = :first_name'; $params[':first_name'] = trim($data->first_name); }
        if (!empty($data->last_name))  { $fields[] = 'last_name = :last_name';   $params[':last_name']  = trim($data->last_name); }

        $phone = null;
        if (isset($data->phone)) {
            $phone = trim((string) $data->phone);
            if ($phone !== '' && !preg_match('/^0[5-7]\d{8}$/', $phone)) {
                http_response_code(400); echo json_encode(['error' => 'Invalid phone number format (e.g. 0550123456)']); exit;
            }
            $fields[] = 'phone = :phone'; $params[':phone'] = $phone ?: null;
        }
        if ($_SESSION['role'] === 'admin' && !empty($data->role)) {
            $fields[] = 'role = :role'; $params[':role'] = $data->role;
        }

        if (empty($fields)) {
            http_response_code(400); echo json_encode(['error' => 'No fields to update']); exit;
        }

        $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        if (!$stmt->execute()) {
            http_response_code(500); echo json_encode(['error' => 'Failed to update user']); exit;
        }

        // Sync to workers table
        $emailRow = $db->prepare('SELECT email FROM users WHERE id = :id');
        $emailRow->execute([':id' => $targetId]);
        $currentEmail = $emailRow->fetchColumn();

        if ($currentEmail) {
            $wFields = []; $wParams = [':email' => $currentEmail];
            if (!empty($data->first_name)) { $wFields[] = 'first_name = :first_name'; $wParams[':first_name'] = trim($data->first_name); }
            if (!empty($data->last_name))  { $wFields[] = 'last_name = :last_name';   $wParams[':last_name']  = trim($data->last_name); }
            if (isset($data->phone))       { $wFields[] = 'phone = :phone';            $wParams[':phone']      = $phone ?: null; }
            if (!empty($wFields)) {
                $wStmt = $db->prepare('UPDATE workers SET ' . implode(', ', $wFields) . ' WHERE email = :email');
                foreach ($wParams as $k => $v) $wStmt->bindValue($k, $v);
                $wStmt->execute();
            }
        }

        // Update session if user edited themselves
        if ((int) $_SESSION['user_id'] === $targetId) {
            if (!empty($data->first_name)) $_SESSION['first_name'] = trim($data->first_name);
            if (!empty($data->last_name))  $_SESSION['last_name']  = trim($data->last_name);
        }

        $updated = $db->prepare('SELECT id, email, first_name, last_name, phone, role, status FROM users WHERE id = :id');
        $updated->execute([':id' => $targetId]);
        echo json_encode(['success' => true, 'message' => 'User updated', 'user' => $updated->fetch(PDO::FETCH_ASSOC)]);
        break;

    case 'DELETE':
        checkRole('admin');
        $data = json_decode(file_get_contents('php://input'));

        if (empty($data->id)) break;
        if ($data->id == $_SESSION['user_id']) {
            http_response_code(403); echo json_encode(['error' => 'Cannot delete your own account']); exit;
        }

        $stmt = $db->prepare('SELECT email, role FROM users WHERE id = :id');
        $stmt->execute([':id' => $data->id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['role'] === 'farmer') {
            http_response_code(403); echo json_encode(['error' => 'Cannot delete the farmer account']); exit;
        }

        $db->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $data->id]);
        if ($user) $db->prepare('DELETE FROM workers WHERE email = :email')->execute([':email' => $user['email']]);

        echo json_encode(['success' => true, 'message' => 'User deleted']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}