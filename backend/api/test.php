<?php
// ============================================================
// CORS HEADERS
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// TEST RESPONSE
// ============================================================
echo json_encode([
    'success' => true,
    'message' => 'PHP is working!',
    'timestamp' => date('Y-m-d H:i:s')
]);
exit;
?>