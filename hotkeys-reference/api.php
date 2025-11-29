<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getVersions':
            $product_id = (int)$_GET['product_id'];
            $stmt = $pdo->prepare("SELECT * FROM product_versions WHERE product_id = ? ORDER BY release_date DESC");
            $stmt->execute([$product_id]);
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'getHotkeys':
            $product_id = (int)$_GET['product_id'];
            $version_id = isset($_GET['version_id']) ? (int)$_GET['version_id'] : null;
            $group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
            
            $sql = "
                SELECT h.*, fg.name as group_name, p.name as product_name, pv.version
                FROM hotkeys h
                JOIN product_versions pv ON h.product_version_id = pv.id
                JOIN products p ON pv.product_id = p.id
                JOIN function_groups fg ON h.function_group_id = fg.id
                WHERE p.id = ?
            ";
            
            $params = [$product_id];
            
            if ($version_id) {
                $sql .= " AND pv.id = ?";
                $params[] = $version_id;
            }
            
            if ($group_id) {
                $sql .= " AND fg.id = ?";
                $params[] = $group_id;
            }
            
            $sql .= " ORDER BY fg.name, h.popularity DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>