<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Если это OPTIONS запрос (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDB();
    
    // GET запрос для получения лошадей
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $params = [];
        $sql = "SELECT * FROM horses WHERE 1=1";
        
        // Поиск по имени
        if (!empty($_GET['search'])) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $_GET['search'] . '%';
        }
        
        // Год рождения от
        if (!empty($_GET['birth_year_from'])) {
            $sql .= " AND birth_year >= ?";
            $params[] = $_GET['birth_year_from'];
        }
        
        // Год рождения до
        if (!empty($_GET['birth_year_to'])) {
            $sql .= " AND birth_year <= ?";
            $params[] = $_GET['birth_year_to'];
        }
        
        // Смерть от
        if (!empty($_GET['death_year_from'])) {
            $sql .= " AND death_year >= ?";
            $params[] = $_GET['death_year_from'];
        }
        
        // Смерть до
        if (!empty($_GET['death_year_to'])) {
            $sql .= " AND death_year <= ?";
            $params[] = $_GET['death_year_to'];
        }
        
        // Количество гонок от
        if (!empty($_GET['races_from'])) {
            $sql .= " AND total_races >= ?";
            $params[] = $_GET['races_from'];
        }
        
        // Количество гонок до
        if (!empty($_GET['races_to'])) {
            $sql .= " AND total_races <= ?";
            $params[] = $_GET['races_to'];
        }
        
        // Победы от
        if (!empty($_GET['wins_from'])) {
            $sql .= " AND total_wins >= ?";
            $params[] = $_GET['wins_from'];
        }
        
        // Победы до
        if (!empty($_GET['wins_to'])) {
            $sql .= " AND total_wins <= ?";
            $params[] = $_GET['wins_to'];
        }
        
        // Поражения от
        if (!empty($_GET['losses_from'])) {
            $sql .= " AND total_losses >= ?";
            $params[] = $_GET['losses_from'];
        }
        
        // Поражения до
        if (!empty($_GET['losses_to'])) {
            $sql .= " AND total_losses <= ?";
            $params[] = $_GET['losses_to'];
        }
        
        // Triple crown
        if (isset($_GET['triple_crown']) && $_GET['triple_crown'] === 'true') {
            $sql .= " AND triple_crown = 1";
        }
        
        // Tiara crown
        if (isset($_GET['tiara_crown']) && $_GET['tiara_crown'] === 'true') {
            $sql .= " AND tiara_crown = 1";
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $horses = $stmt->fetchAll();
        
        echo json_encode($horses);
        exit;
    }
    
    // POST запрос (если понадобится)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Ваша логика для POST...
        echo json_encode(['success' => true, 'message' => 'POST request received']);
        exit;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
    exit;
}

// Если метод не поддерживается
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>