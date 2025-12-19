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
        // Статистика жокеев
        if (isset($_GET['stats'])) {
            // Основной запрос жокеев
            $sql = "
                SELECT 
                    j.*, 
                    COUNT(re.id) as total_entries
                FROM jockeys j
                LEFT JOIN race_entries re ON j.license_number = re.license_number
                GROUP BY j.id
                ORDER BY j.name ASC
            ";
            
            $stmt = $db->query($sql);
            $jockeys = $stmt->fetchAll();
            
            // Добавляем последние заявки для каждого жокея
            foreach ($jockeys as &$jockey) {
                $stmt = $db->prepare("
                    SELECT re.*, r.name as race_name
                    FROM race_entries re
                    LEFT JOIN races r ON re.race_id = r.id
                    WHERE re.license_number = ?
                    ORDER BY re.created_at DESC
                    LIMIT 10
                ");
                
                $stmt->execute([$jockey['license_number']]);
                $jockey['race_entries'] = $stmt->fetchAll();
            }
            
            // Общая статистика
            $totalEntries = array_sum(array_column($jockeys, 'total_entries'));
            
            echo json_encode([
                'jockeys' => $jockeys,
                'stats' => [
                    'totalJockeys' => count($jockeys),
                    'totalEntries' => $totalEntries
                ]
            ]);
            exit;
        }
        
        // Профиль конкретного жокея
        if (!empty($_GET['id'])) {
            $stmt = $db->prepare("
                SELECT id, name, username, license_number, created_at 
                FROM jockeys 
                WHERE id = ?
            ");
            
            $stmt->execute([$_GET['id']]);
            $jockey = $stmt->fetch();
            
            if ($jockey) {
                echo json_encode($jockey);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Jockey not found']);
            }
            exit;
        }
        
        // Все жокеи
        $stmt = $db->query("SELECT id, name, license_number FROM jockeys ORDER BY name");
        echo json_encode($stmt->fetchAll());
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