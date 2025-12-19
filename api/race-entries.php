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
    
    // ПОЛУЧЕНИЕ заявок пользователя
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!empty($_GET['user_id'])) {
            $userId = $_GET['user_id'];
            
            // Получаем информацию о жокее
            $stmt = $db->prepare("SELECT id, name, license_number FROM jockeys WHERE id = ?");
            $stmt->execute([$userId]);
            $jockey = $stmt->fetch();
            
            if (!$jockey) {
                http_response_code(404);
                echo json_encode(['error' => 'Jockey not found']);
                exit;
            }
            
            // Получаем заявки жокея
            $stmt = $db->prepare("
                SELECT re.*, r.name as race_name, 
                       h.name as horse_name, rc.name as racecourse_name
                FROM race_entries re
                LEFT JOIN races r ON re.race_id = r.id
                LEFT JOIN horses h ON re.horse_id = h.id
                LEFT JOIN racecourses rc ON r.racecourse_id = rc.id
                WHERE re.license_number = ?
                ORDER BY re.created_at DESC
            ");
            
            $stmt->execute([$jockey['license_number']]);
            $entries = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'jockey' => $jockey,
                'entries' => $entries
            ]);
            exit;
        }
        
        // Все заявки (для админа)
        $stmt = $db->query("
            SELECT re.*, r.name as race_name, h.name as horse_name,
                   j.name as jockey_name, rc.name as racecourse_name
            FROM race_entries re
            LEFT JOIN races r ON re.race_id = r.id
            LEFT JOIN horses h ON re.horse_id = h.id
            LEFT JOIN jockeys j ON re.license_number = j.license_number
            LEFT JOIN racecourses rc ON r.racecourse_id = rc.id
            ORDER BY re.created_at DESC
        ");
        
        echo json_encode($stmt->fetchAll());
        exit;
    }
    
    // СОЗДАНИЕ новой заявки
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Валидация
        $required = ['jockeyName', 'licenseNumber', 'horseId', 'raceId', 
                    'saddlecloth', 'barrier', 'declaredWeight'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
                exit;
            }
        }
        
        // Проверка веса
        $weight = floatval($data['declaredWeight']);
        if ($weight < 50 || $weight > 70) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Weight must be between 50-70kg']);
            exit;
        }
        
        // Вставка в БД
        $stmt = $db->prepare("
            INSERT INTO race_entries 
            (jockey_name, license_number, horse_id, race_id, 
             saddlecloth, barrier, declared_weight, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $data['jockeyName'],
            $data['licenseNumber'],
            $data['horseId'],
            $data['raceId'],
            $data['saddlecloth'],
            $data['barrier'],
            $data['declaredWeight']
        ]);
        
        $entryId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'entryId' => $entryId,
            'message' => 'Race entry submitted successfully'
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