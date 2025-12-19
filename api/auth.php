<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDB();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // ЛОГИН
        if (isset($data['username']) && isset($data['password'])) {
            $username = trim($data['username']);
            $password = trim($data['password']);
            
            $stmt = $db->prepare("SELECT * FROM jockeys WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && $password === $user['password']) {
                // В реальном проекте используйте password_verify()!
                unset($user['password']);
                
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_license'] = $user['license_number'];
                
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'username' => $user['username'],
                        'license_number' => $user['license_number']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
            exit;
        }
        
        // РЕГИСТРАЦИЯ
        if (isset($data['fullName']) && isset($data['username']) && 
            isset($data['password']) && isset($data['licenseNumber'])) {
            
            // Проверка существующего пользователя
            $stmt = $db->prepare("SELECT id FROM jockeys WHERE username = ?");
            $stmt->execute([$data['username']]);
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                exit;
            }
            
            // Создание пользователя
            $stmt = $db->prepare("
                INSERT INTO jockeys (name, username, password, license_number) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['fullName'],
                $data['username'],
                $data['password'], // В реальном проекте: password_hash()
                $data['licenseNumber']
            ]);
            
            $userId = $db->lastInsertId();
            
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $data['fullName'];
            $_SESSION['user_license'] = $data['licenseNumber'];
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'name' => $data['fullName'],
                    'username' => $data['username'],
                    'license_number' => $data['licenseNumber']
                ]
            ]);
            exit;
        }
        
        // ЛОГАУТ
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            session_start();
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logged out']);
            exit;
        }
        
        // ПРОВЕРКА СЕССИИ
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            session_start();
            if (isset($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'license_number' => $_SESSION['user_license']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?>