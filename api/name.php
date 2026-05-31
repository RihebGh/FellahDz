<?php
// name.php — API for managing worker field assignments

require_once '../config/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = (new Database())->getConnection();

    // Special GET actions
    if ($method === 'GET' && isset($_GET['action'])) {
        if ($_GET['action'] === 'workers') {
            $rows = $conn->query("SELECT id, first_name, last_name,
                                  CONCAT(first_name, ' ', last_name) AS full_name, email, phone
                                  FROM workers WHERE status = 'active' AND role = 'worker'
                                  ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }

        if ($_GET['action'] === 'get_worker_by_email' && isset($_GET['email'])) {
            $stmt = $conn->prepare("SELECT id, first_name, last_name,
                                    CONCAT(first_name, ' ', last_name) AS full_name,
                                    email, phone, role, status
                                    FROM workers WHERE email = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$_GET['email']]);
            $worker = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($worker) {
                echo json_encode(['success' => true, 'data' => $worker]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Worker not found with this email']);
            }
            exit;
        }
    }

    switch ($method) {

        case 'GET':
            // Get all field assignments grouped by field number
            $stmt = $conn->query("SELECT fa.id, fa.field_number, fa.worker_id,
                                  w.first_name, w.last_name,
                                  CONCAT(w.first_name, ' ', w.last_name) AS worker_name,
                                  w.email AS worker_email,
                                  fa.planted, fa.irrigation, fa.pesticides, fa.notes, fa.updated_at
                                  FROM field_assignments fa
                                  INNER JOIN workers w ON fa.worker_id = w.id
                                  ORDER BY fa.field_number, w.last_name");

            $grouped = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $grouped[$row['field_number']][] = $row;
            }
            echo json_encode(['success' => true, 'data' => $grouped]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (($data['action'] ?? '') === 'add_assignment') {
                // Add a worker to a field
                if (!isset($data['field_number'], $data['worker_id'])) {
                    http_response_code(400); echo json_encode(['error' => 'Missing field_number or worker_id']); exit;
                }
                $fieldNum = (int) $data['field_number'];
                $workerId = (int) $data['worker_id'];

                $workerStmt = $conn->prepare("SELECT id, first_name, last_name FROM workers WHERE id = ? AND status = 'active'");
                $workerStmt->execute([$workerId]);
                $worker = $workerStmt->fetch(PDO::FETCH_ASSOC);
                if (!$worker) {
                    http_response_code(404); echo json_encode(['error' => 'Worker not found or inactive']); exit;
                }

                $dupStmt = $conn->prepare("SELECT id FROM field_assignments WHERE field_number = ? AND worker_id = ?");
                $dupStmt->execute([$fieldNum, $workerId]);
                if ($dupStmt->fetch()) {
                    http_response_code(409); echo json_encode(['error' => 'Worker already assigned to this field']); exit;
                }

                $conn->prepare("INSERT INTO field_assignments (field_number, worker_id, planted, irrigation, pesticides, notes)
                                VALUES (?, ?, 0, 0, NULL, ?)")
                     ->execute([$fieldNum, $workerId, $data['notes'] ?? null]);

                echo json_encode(['success' => true, 'message' => 'Worker assigned to field successfully',
                    'data' => ['id' => $conn->lastInsertId(), 'field_number' => $fieldNum,
                               'worker_id' => $workerId, 'worker_name' => $worker['first_name'] . ' ' . $worker['last_name'],
                               'first_name' => $worker['first_name'], 'last_name' => $worker['last_name']]]);
            } else {
                // Change which worker is assigned (farmer action)
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['assignment_id'], $data['new_worker_id'])) {
                    http_response_code(400); echo json_encode(['error' => 'Missing assignment_id or new_worker_id']); exit;
                }
                $assignId = (int) $data['assignment_id'];
                $newWorkerId = (int) $data['new_worker_id'];

                $workerStmt = $conn->prepare("SELECT id, first_name, last_name FROM workers WHERE id = ? AND status = 'active'");
                $workerStmt->execute([$newWorkerId]);
                $worker = $workerStmt->fetch(PDO::FETCH_ASSOC);
                if (!$worker) {
                    http_response_code(404); echo json_encode(['error' => 'Worker not found or inactive']); exit;
                }

                $curStmt = $conn->prepare("SELECT field_number FROM field_assignments WHERE id = ?");
                $curStmt->execute([$assignId]);
                $current = $curStmt->fetch(PDO::FETCH_ASSOC);
                if (!$current) {
                    http_response_code(404); echo json_encode(['error' => 'Assignment not found']); exit;
                }

                $dupStmt = $conn->prepare("SELECT id FROM field_assignments WHERE field_number = ? AND worker_id = ? AND id != ?");
                $dupStmt->execute([$current['field_number'], $newWorkerId, $assignId]);
                if ($dupStmt->fetch()) {
                    http_response_code(409); echo json_encode(['error' => 'Worker already assigned to this field']); exit;
                }

                $conn->prepare("UPDATE field_assignments SET worker_id = ? WHERE id = ?")->execute([$newWorkerId, $assignId]);
                echo json_encode(['success' => true, 'message' => 'Worker updated successfully',
                    'data' => ['assignment_id' => $assignId, 'new_worker_id' => $newWorkerId,
                               'worker_name' => $worker['first_name'] . ' ' . $worker['last_name'],
                               'first_name' => $worker['first_name'], 'last_name' => $worker['last_name']]]);
            }
            break;

        case 'PUT':
            // Update activities: planted, irrigation, pesticides (worker dashboard)
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['assignment_id'])) {
                http_response_code(400); echo json_encode(['error' => 'Missing assignment_id']); exit;
            }

            $fields = []; $params = [];
            if (isset($data['planted']))    { $fields[] = "planted = ?";    $params[] = $data['planted']    ? 1 : 0; }
            if (isset($data['irrigation'])) { $fields[] = "irrigation = ?"; $params[] = $data['irrigation'] ? 1 : 0; }
            if (isset($data['pesticides'])) { $fields[] = "pesticides = ?"; $params[] = $data['pesticides']; }

            if (empty($fields)) {
                http_response_code(400); echo json_encode(['error' => 'No fields to update']); exit;
            }

            $params[] = (int) $data['assignment_id'];
            $conn->prepare("UPDATE field_assignments SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
            echo json_encode(['success' => true, 'message' => 'Activities updated successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}