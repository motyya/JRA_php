<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDB();
    
    // ПОЛУЧЕНИЕ избранного
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (empty($_GET['user_id']) || empty($_GET['type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            exit;
        }
        
        $userId = $_GET['user_id'];
        $type = $_GET['type'];
        
        switch ($type) {
            case 'horses':
                $table = 'user_favorite_horses';
                $joinTable = 'horses';
                $joinId = 'horse_id';
                break;
                
            case 'races':
                $table = 'user_favorite_races';
                $joinTable = 'races';
                $joinId = 'race_id';
                break;
                
            case 'racecourses':
                $table = 'user_favorite_racecourses';
                $joinTable = 'racecourses';
                $joinId = 'racecourse_id';
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type']);
                exit;
        }
        
        $sql = "SELECT t.* FROM $joinTable t
                JOIN $table uf ON t.id = uf.$joinId
                WHERE uf.user_id = ?
                ORDER BY uf.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        
        echo json_encode($results);
        exit;
    }
    
    // ДОБАВЛЕНИЕ в избранное
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['user_id']) || empty($data['type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            exit;
        }
        
        $userId = $data['user_id'];
        $type = $data['type'];
        
        switch ($type) {
            case 'horses':
                $table = 'user_favorite_horses';
                $idField = 'horse_id';
                $idValue = $data['horse_id'] ?? null;
                break;
                
            case 'races':
                $table = 'user_favorite_races';
                $idField = 'race_id';
                $idValue = $data['race_id'] ?? null;
                break;
                
            case 'racecourses':
                $table = 'user_favorite_racecourses';
                $idField = 'racecourse_id';
                $idValue = $data['racecourse_id'] ?? null;
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type']);
                exit;
        }
        
        if (!$idValue) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing item ID']);
            exit;
        }
        
        // Проверяем, существует ли уже
        $checkSql = "SELECT id FROM $table WHERE user_id = ? AND $idField = ?";
        $stmt = $db->prepare($checkSql);
        $stmt->execute([$userId, $idValue]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Already in favorites']);
            exit;
        }
        
        // Добавляем
        $insertSql = "INSERT INTO $table (user_id, $idField, created_at) VALUES (?, ?, NOW())";
        $stmt = $db->prepare($insertSql);
        $stmt->execute([$userId, $idValue]);
        
        echo json_encode(['success' => true, 'message' => 'Added to favorites']);
        exit;
    }
    
    // УДАЛЕНИЕ из избранного
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['user_id']) || empty($data['type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            exit;
        }
        
        $userId = $data['user_id'];
        $type = $data['type'];
        
        switch ($type) {
            case 'horses':
                $table = 'user_favorite_horses';
                $idField = 'horse_id';
                $idValue = $data['horse_id'] ?? null;
                break;
                
            case 'races':
                $table = 'user_favorite_races';
                $idField = 'race_id';
                $idValue = $data['race_id'] ?? null;
                break;
                
            case 'racecourses':
                $table = 'user_favorite_racecourses';
                $idField = 'racecourse_id';
                $idValue = $data['racecourse_id'] ?? null;
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type']);
                exit;
        }
        
        if (!$idValue) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing item ID']);
            exit;
        }
        
        $deleteSql = "DELETE FROM $table WHERE user_id = ? AND $idField = ?";
        $stmt = $db->prepare($deleteSql);
        $stmt->execute([$userId, $idValue]);
        
        echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
        exit;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>