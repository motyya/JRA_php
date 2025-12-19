<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDB();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $params = [];
        $sql = "SELECT * FROM racecourses WHERE 1=1";
        
        // Поиск
        if (!empty($_GET['search'])) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $_GET['search'] . '%';
        }
        
        // Трасса
        if (!empty($_GET['track'])) {
            $sql .= " AND track_types LIKE ?";
            $params[] = '%' . $_GET['track'] . '%';
        }
        
        // Направление
        if (!empty($_GET['direction'])) {
            $sql .= " AND direction = ?";
            $params[] = $_GET['direction'];
        }
        
        // Углы
        if (!empty($_GET['corners'])) {
            $sql .= " AND corners = ?";
            $params[] = $_GET['corners'];
        }
        
        // Дистанция от/до
        if (!empty($_GET['distance_from'])) {
            $sql .= " AND main_distance >= ?";
            $params[] = $_GET['distance_from'];
        }
        
        if (!empty($_GET['distance_to'])) {
            $sql .= " AND main_distance <= ?";
            $params[] = $_GET['distance_to'];
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $racecourses = $stmt->fetchAll();
        
        echo json_encode($racecourses);
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