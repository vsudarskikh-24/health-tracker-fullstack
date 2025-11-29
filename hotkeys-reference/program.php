<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Получение информации о программе
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Получение версий
$stmt = $pdo->prepare("SELECT * FROM product_versions WHERE product_id = ? ORDER BY release_date DESC");
$stmt->execute([$product_id]);
$versions = $stmt->fetchAll();

// Получение горячих клавиш для последней версии
$version_id = isset($_GET['version']) ? (int)$_GET['version'] : ($versions[0]['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        h.*,
        fg.name as group_name
    FROM hotkeys h
    JOIN function_groups fg ON h.function_group_id = fg.id
    WHERE h.product_version_id = ?
    ORDER BY fg.name, h.popularity DESC
");
$stmt->execute([$version_id]);
$hotkeys = $stmt->fetchAll();

// Группировка по функциональным группам
$grouped_hotkeys = [];
foreach ($hotkeys as $hotkey) {
    $grouped_hotkeys[$hotkey['group_name']][] = $hotkey;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product['name']) ?> - Горячие клавиши</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1>⌨️ Справочник горячих клавиш</h1>
                <nav>
                    <a href="index.php">Главная</a>
                    <a href="compare.php">Сравнение версий</a>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="breadcrumbs">
                <a href="index.php">Главная</a> / <?= e($product['name']) ?>
            </div>

            <div class="product-header">
                <h1><?= e($product['name']) ?></h1>
                <p><?= e($product['description']) ?></p>
                <span class="category-badge"><?= e($product['category']) ?></span>
            </div>

            <div class="version-selector">
                <label>Выберите версию:</label>
                <?php foreach ($versions as $version): ?>
                    <a href="program.php?id=<?= $product_id ?>&version=<?= $version['id'] ?>" 
                       class="version-btn <?= $version['id'] == $version_id ? 'active' : '' ?>">
                        v<?= e($version['version']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php foreach ($grouped_hotkeys as $group_name => $group_hotkeys): ?>
                <section class="hotkeys-section">
                    <h2><?= e($group_name) ?></h2>
                    <table class="hotkeys-table">
                        <thead>
                            <tr>
                                <th>Горячие клавиши</th>
                                <th>Действие</th>
                                <th>Дополнительно</th>
                                <th>Популярность</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group_hotkeys as $hotkey): ?>
                                <tr>
                                    <td class="keys-cell"><?= formatKeys($hotkey['key_combination']) ?></td>
                                    <td><?= e($hotkey['action_description']) ?></td>
                                    <td><?= e($hotkey['additional_info']) ?></td>
                                    <td>
                                        <span class="popularity-badge">
                                            👁️ <?= $hotkey['popularity'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш</p>
        </div>
    </footer>
</body>
</html>

<?php
function formatKeys($keys) {
    $parts = explode('+', $keys);
    $formatted = '';
    foreach ($parts as $part) {
        $formatted .= '<kbd>' . htmlspecialchars($part, ENT_QUOTES, 'UTF-8') . '</kbd>';
        if ($part !== end($parts)) {
            $formatted .= '<span class="plus">+</span>';
        }
    }
    return $formatted;
}
?>