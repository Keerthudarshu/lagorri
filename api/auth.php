<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'POST':
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'register':
                    $result = handleRegister($input);
                    echo json_encode($result);
                    break;
                    
                case 'login':
                    $result = handleLogin($input);
                    echo json_encode($result);
                    break;
                    
                case 'logout':
                    $result = handleLogout();
                    echo json_encode($result);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'GET':
            // Check if user is logged in
            $result = getCurrentUser();
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleRegister($data) {
    // Validate input
    if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
        throw new Exception('All fields are required');
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    if (strlen($data['password']) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    try {
        // Create new user using database function
        $user = createUser($data);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        return [
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user
        ];
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handleLogin($data) {
    if (!isset($data['email'], $data['password'])) {
        throw new Exception('Email and password are required');
    }
    
    try {
        // Authenticate user using database function
        $user = authenticateUser($data['email'], $data['password']);
        
        if (!$user) {
            throw new Exception('Invalid email or password');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handleLogout() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'Logged out successfully'
    ];
}

function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name']
            ]
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Not logged in'
    ];
}
    
    // Load users
    $users = loadJsonData('users.json');
    
    // Find user
    $user = null;
    $userIndex = -1;
    foreach ($users as $index => $u) {
        if ($u['email'] === $email) {
            $user = $u;
            $userIndex = $index;
            break;
        }
    }
    
    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('Invalid email or password');
    }
    
    // Update last login
    $users[$userIndex]['last_login'] = date('Y-m-d H:i:s');
    saveJsonData('users.json', $users);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['firstName'] . ' ' . $user['lastName']
        ]
    ];
}

function logoutUser() {
    session_destroy();
    session_start();
    
    return [
        'success' => true,
        'message' => 'Logout successful'
    ];
}

function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name']
            ]
        ];
    }
    
    return [
        'success' => false,
        'message' => 'User not logged in'
    ];
}
?>
