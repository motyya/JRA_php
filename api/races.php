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
        $sql = "SELECT r.*, rc.name as racecourse_name 
                FROM races r 
                LEFT JOIN racecourses rc ON r.racecourse_id = rc.id 
                WHERE 1=1";
        
        // Поиск
        if (!empty($_GET['search'])) {
            $sql .= " AND (r.name LIKE ? OR rc.name LIKE ?)";
            $params[] = '%' . $_GET['search'] . '%';
            $params[] = '%' . $_GET['search'] . '%';
        }
        
        // Ипподром
        if (!empty($_GET['racecourse'])) {
            $sql .= " AND rc.name = ?";
            $params[] = $_GET['racecourse'];
        }
        
        // Направление
        if (!empty($_GET['direction'])) {
            $sql .= " AND r.direction = ?";
            $params[] = $_GET['direction'];
        }
        
        // Сезон
        if (!empty($_GET['season'])) {
            $sql .= " AND r.season = ?";
            $params[] = $_GET['season'];
        }
        
        // Трасса
        if (!empty($_GET['track'])) {
            $sql .= " AND r.track_type = ?";
            $params[] = $_GET['track'];
        }
        
        // Ранг
        if (!empty($_GET['rang'])) {
            $sql .= " AND r.rang = ?";
            $params[] = $_GET['rang'];
        }
        
        // Дистанция от/до
        if (!empty($_GET['distance_from'])) {
            $sql .= " AND r.distance >= ?";
            $params[] = $_GET['distance_from'];
        }
        
        if (!empty($_GET['distance_to'])) {
            $sql .= " AND r.distance <= ?";
            $params[] = $_GET['distance_to'];
        }
        
        // Тип дистанции
        if (!empty($_GET['distance_type'])) {
            switch ($_GET['distance_type']) {
                case 'sprint':
                    $sql .= " AND r.distance BETWEEN 1000 AND 1200";
                    break;
                case 'mile':
                    $sql .= " AND r.distance BETWEEN 1400 AND 1600";
                    break;
                case 'medium':
                    $sql .= " AND r.distance BETWEEN 1700 AND 2200";
                    break;
                case 'long':
                    $sql .= " AND r.distance BETWEEN 2300 AND 3200";
                    break;
            }
        }
        
        $sql .= " ORDER BY r.name";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $races = $stmt->fetchAll();
        
        echo json_encode($races);
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