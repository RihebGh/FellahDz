<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$jsonFile = __DIR__ . '/farm-location-data.json';
$userId   = $_SESSION['user_id'] ?? 1; // Fallback to 1 for testing
$method   = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Load from JSON file if saved, otherwise return default
    $allData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    $location = $allData[$userId] ?? [
        'name' => 'Jijel, Algeria', 'lat' => 36.8206, 'lon' => 5.7667, 'updated_at' => null
    ];
    echo json_encode(['success' => true, 'location' => $location]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']); exit;
    }

    $name = trim($data['name'] ?? '');
    $lat  = floatval($data['lat'] ?? 0);
    $lon  = floatval($data['lon'] ?? 0);

    if (!$name || !$lat || !$lon) {
        echo json_encode(['success' => false, 'error' => 'Invalid data: name, lat, or lon missing']); exit;
    }

    $locationData = ['name' => $name, 'lat' => $lat, 'lon' => $lon, 'updated_at' => date('Y-m-d H:i:s')];

    $allData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    $allData[$userId] = $locationData;

    if (file_put_contents($jsonFile, json_encode($allData, JSON_PRETTY_PRINT)) === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to write to file. Check permissions.']); exit;
    }

    echo json_encode(['success' => true, 'message' => 'Location saved successfully', 'location' => $locationData]);

} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed: ' . $method]);
}