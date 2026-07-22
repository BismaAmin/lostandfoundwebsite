<?php
// ============================================================
// CORS HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// ✅ DIRECT DATABASE CONNECTION (NO external file)
// ============================================================
try {
    $host = 'localhost';
    $dbname = 'lost_found_db';
    $username = 'root';
    $password = '';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

if (empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password is required'
    ]);
    exit;
}

try {
    // ============================================================
    // ✅ READ ROLE FROM DATABASE - NOT FROM FRONTEND!
    // ============================================================
    $stmt = $db->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    if ($user['status'] !== 'Active') {
        echo json_encode([
            'success' => false,
            'message' => 'Account is inactive. Please contact support.'
        ]);
        exit;
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // ============================================================
    // ✅ ROLE IS FROM DATABASE - SECURE!
    // ============================================================
    $role = $user['role']; // 'Student', 'Manager', or 'Admin'
    
    // Get redirect URL based on role
    $redirectUrl = getRedirectUrl($role);
    
    // Generate JWT
    $jwtSecret = 'your-super-secret-jwt-key-change-this-in-production';
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'email' => $user['email'],
        'role' => $role, // ✅ Role from database
        'userId' => $user['id'],
        'exp' => time() + (60 * 60 * 24 * 7) // 7 days
    ]));
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $jwtSecret, true));
    $token = "$header.$payload.$signature";
    
    // ✅ Return role and redirect URL
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'redirect' => $redirectUrl, // ✅ Frontend can use this
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $role, // ✅ Role from database
            'status' => $user['status']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// ============================================================
// ✅ HELPER FUNCTION: Get redirect URL based on role
// ============================================================
function getRedirectUrl($role) {
    switch ($role) {
        case 'Admin':
            return '../admin/dashboard.php';
        case 'Manager':
            return '../manager/dashboard.php';
        case 'Student':
        default:
            return '../pub/home.html';
    }
}
?>