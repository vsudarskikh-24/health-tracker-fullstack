<?php
require_once 'config.php';

// Получение данных для PDF
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$version_id = isset($_GET['version_id']) ? (int)$_GET['version_id'] : 0;

$stmt = $pdo->prepare("
    SELECT h.*, fg.name as group_name, p.name as product_name, pv.version
    FROM hotkeys h
    JOIN product_versions pv ON h.product_version_id = pv.id
    JOIN products p ON pv.product_id = p.id
    JOIN function_groups fg ON h.function_group_id = fg.id
    WHERE 1=1
    " . ($product_id ? " AND p.id = ?" : "") . "
    " . ($version_id ? " AND pv.id = ?" : "") . "
    ORDER BY fg.name, h.key_combination
");

$params = array_filter([$product_id, $version_id]);
$stmt->execute($params);
$hotkeys = $stmt->fetchAll();

// Группировка
$grouped = [];
foreach ($hotkeys as $hotkey) {
    $grouped[$hotkey['group_name']][] = $hotkey;
}

// Генерация HTML для PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 20px; background: #f0f0f0; padding: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #007bff; color: white; }
        kbd { background: #eee; padding: 3px 6px; border: 1px solid #ccc; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Шпаргалка по горячим клавишам</h1>
';

foreach ($grouped as $group_name => $group_hotkeys) {
    $html .= "<h2>" . htmlspecialchars($group_name) . "</h2>";
    $html .= '<table><thead><tr><th>Клавиши</th><th>Действие</th></tr></thead><tbody>';
    foreach ($group_hotkeys as $hotkey) {
        $keys = htmlspecialchars($hotkey['key_combination']);
        $action = htmlspecialchars($hotkey['action_description']);
        $html .= "<tr><td><kbd>$keys</kbd></td><td>$action</td></tr>";
    }
    $html .= '</tbody></table>';
}

$html .= '</body></html>';

// Для реальной генерации PDF используйте библиотеку типа TCPDF или Dompdf
// Здесь просто возвращаем HTML
header('Content-Type: text/html; charset=UTF-8');
echo $html;

// Альтернативно можно использовать встроенный print:
// echo '<script>window.print();</script>';
?>