<?php
require_once 'config.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['results' => []]);
    exit;
}

try {
    // Поиск по клавишам и описанию действия
    $stmt = $pdo->prepare("
        SELECT 
            h.id,
            h.key_combination,
            h.action_description,
            h.additional_info,
            h.popularity,
            p.name as product_name,
            p.id as product_id,
            pv.version,
            fg.name as group_name
        FROM hotkeys h
        JOIN product_versions pv ON h.product_version_id = pv.id
        JOIN products p ON pv.product_id = p.id
        JOIN function_groups fg ON h.function_group_id = fg.id
        WHERE h.key_combination LIKE :query1
           OR h.action_description LIKE :query2
           OR p.name LIKE :query3
        ORDER BY 
            CASE 
                WHEN h.key_combination = :exact THEN 0
                WHEN h.key_combination LIKE :startsWith THEN 1
                ELSE 2
            END,
            h.popularity DESC
        LIMIT 20
    ");
    
    $likeQuery = '%' . $query . '%';
    $startsWith = $query . '%';
    
    $stmt->execute([
        'query1' => $likeQuery,
        'query2' => $likeQuery,
        'query3' => $likeQuery,
        'exact' => $query,
        'startsWith' => $startsWith
    ]);
    
    $results = $stmt->fetchAll();
    
    // Увеличиваем популярность найденных клавиш
    if (!empty($results)) {
        $ids = array_column($results, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $updateStmt = $pdo->prepare("UPDATE hotkeys SET popularity = popularity + 1 WHERE id IN ($placeholders)");
        $updateStmt->execute($ids);
    }
    
    echo json_encode(['results' => $results]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Ошибка поиска', 'message' => $e->getMessage()]);
}
?>