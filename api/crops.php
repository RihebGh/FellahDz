<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$pdo    = (new Database())->getConnection();
$userId = 1; // Replace with session user in production
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        case 'GET':
            // Return crop types list
            if (($_GET['action'] ?? '') === 'types') {
                $rows = $pdo->query("SELECT name, emoji, color_scheme FROM crop_types WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $rows]);
                break;
            }

            // Return structured crops grouped by type
            $stmt = $pdo->prepare("
                SELECT c.*, ABS(DATEDIFF(COALESCE(c.status_changed_at, c.created_at), CURDATE())) AS days_in_stage
                FROM crops c
                WHERE c.user_id = ? AND c.is_active = 1
                ORDER BY c.display_order, c.id
            ");
            $stmt->execute([$userId]);

            $structured = ['lettuce' => null, 'tomato' => null, 'onion' => null, 'mixed' => []];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $crop) {
                if ($crop['is_mixed']) {
                    $structured['mixed'][] = $crop;
                } else {
                    $structured[strtolower(str_replace(' ', '_', $crop['crop_type']))] = $crop;
                }
            }
            echo json_encode(['success' => true, 'data' => $structured]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['crops']) || !is_array($data['crops'])) throw new Exception('Invalid data format');

            $pdo->beginTransaction();

            foreach ($data['crops'] as $crop) {
                // Get emoji and color for crop type
                $typeStmt = $pdo->prepare("SELECT emoji, color_scheme FROM crop_types WHERE name = ?");
                $typeStmt->execute([$crop['crop_type']]);
                $type = $typeStmt->fetch(PDO::FETCH_ASSOC);

                // Check previous status to detect changes
                $oldStatus = $pdo->prepare("SELECT crop_status FROM crops WHERE id = ? AND user_id = ?");
                $oldStatus->execute([$crop['id'], $userId]);
                $prevStatus = $oldStatus->fetchColumn();

                $statusChanged = $prevStatus !== $crop['crop_status'];
                $extraField    = $statusChanged ? ", status_changed_at = NOW()" : "";

                $pdo->prepare("UPDATE crops SET
                    crop_name = ?, crop_type = ?, crop_emoji = ?, crop_color = ?,
                    crop_status = ?, health_status = ?, planting_date = ?,
                    harvest_date = ?, water_per_m2 = ?, updated_at = NOW() $extraField
                    WHERE id = ? AND user_id = ?")
                    ->execute([
                        $crop['crop_name'], $crop['crop_type'],
                        $type['emoji'] ?? '🌱', $type['color_scheme'] ?? 'rgba(52,152,219,.1)|#2980b9',
                        $crop['crop_status'] ?? 'planted', $crop['health_status'] ?? 'good',
                        $crop['planting_date'], $crop['harvest_date'], $crop['water_per_m2'],
                        $crop['id'], $userId,
                    ]);

                // Log status change history
                if ($statusChanged) {
                    $pdo->prepare("INSERT INTO crop_status_history (crop_id, previous_status, new_status, changed_by, change_reason)
                                   VALUES (?, ?, ?, ?, ?)")
                        ->execute([$crop['id'], $prevStatus, $crop['crop_status'], $userId, 'Updated from Crop Management page']);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'All crops updated successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}