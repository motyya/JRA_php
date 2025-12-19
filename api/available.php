<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDB();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Доступные забеги
        if (isset($_GET['type']) && $_GET['type'] === 'races') {
            $stmt = $db->query("SELECT id, name FROM races ORDER BY name");
            $races = $stmt->fetchAll();
            echo json_encode($races);
            exit;
        }
        
        // Доступные лошади
        if (isset($_GET['type']) && $_GET['type'] === 'horses') {
            $stmt = $db->query("SELECT id, name FROM horses ORDER BY name");
            $horses = $stmt->fetchAll();
            echo json_encode($horses);
            exit;
        }
        
        // По умолчанию возвращаем оба списка
        $stmt1 = $db->query("SELECT id, name FROM races ORDER BY name");
        $stmt2 = $db->query("SELECT id, name FROM horses ORDER BY name");
        
        echo json_encode([
            'races' => $stmt1->fetchAll(),
            'horses' => $stmt2->fetchAll()
        ]);
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