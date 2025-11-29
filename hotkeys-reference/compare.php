<?php
require_once 'config.php';

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$comparison_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $version1_id = (int)$_POST['version1'];
    $version2_id = (int)$_POST['version2'];
    
    // Получение данных для сравнения
    $stmt = $pdo->prepare("
        SELECT h.*, fg.name as group_name
        FROM hotkeys h
        JOIN function_groups fg ON h.function_group_id = fg.id
        WHERE h.product_version_id IN (?, ?)
        ORDER BY h.key_combination
    ");
    $stmt->execute([$version1_id, $version2_id]);
    $all_hotkeys = $stmt->fetchAll();
    
    // Группировка по клавишам
    foreach ($all_hotkeys as $hotkey) {
        $key = $hotkey['key_combination'];
        if (!isset($comparison_data[$key])) {
            $comparison_data[$key] = [
                'version1' => null,
                'version2' => null
            ];
        }
        
        if ($hotkey['product_version_id'] == $version1_id) {
            $comparison_data[$key]['version1'] = $hotkey;
        } else {
            $comparison_data[$key]['version2'] = $hotkey;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сравнение версий</title>
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
            <h1>Сравнение версий программ</h1>
            
            <form method="POST" class="compare-form">
                <div class="compare-selector">
                    <div class="version-select">
                        <label>Программа:</label>
                        <select id="product1" name="product1" required>
                            <option value="">Выберите программу</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= e($product['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <label>Версия 1:</label>
                        <select id="version1" name="version1" required>
                            <option value="">Сначала выберите программу</option>
                        </select>
                    </div>

                    <div class="vs-divider">VS</div>

                    <div class="version-select">
                        <label>Версия 2:</label>
                        <select id="version2" name="version2" required>
                            <option value="">Сначала выберите программу</option>
                        </select>
                    </div>
                </div>

                <button type="submit">Сравнить</button>
            </form>

            <?php if (!empty($comparison_data)): ?>
                <div class="comparison-results">
                    <h2>Результаты сравнения</h2>
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th>Клавиши</th>
                                <th>Версия 1</th>
                                <th>Версия 2</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comparison_data as $keys => $data): ?>
                                <?php
                                $status = '';
                                if ($data['version1'] && $data['version2']) {
                                    if ($data['version1']['action_description'] === $data['version2']['action_description']) {
                                        $status = '<span class="status-same">Без изменений</span>';
                                    } else {
                                        $status = '<span class="status-changed">Изменено</span>';
                                    }
                                } elseif ($data['version1']) {
                                    $status = '<span class="status-removed">Удалено</span>';
                                } else {
                                    $status = '<span class="status-added">Добавлено</span>';
                                }
                                ?>
                                <tr>
                                    <td class="keys-cell"><?= formatKeys($keys) ?></td>
                                    <td><?= $data['version1'] ? e($data['version1']['action_description']) : '—' ?></td>
                                    <td><?= $data['version2'] ? e($data['version2']['action_description']) : '—' ?></td>
                                    <td><?= $status ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script>
        // Загрузка версий при выборе программы
        document.getElementById('product1').addEventListener('change', function() {
            loadVersions(this.value, 'version1');
            loadVersions(this.value, 'version2');
        });

        function loadVersions(productId, selectId) {
            if (!productId) return;
            
            fetch(`api.php?action=getVersions&product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    const select = document.getElementById(selectId);
                    select.innerHTML = '<option value="">Выберите версию</option>';
                    data.forEach(v => {
                        select.innerHTML += `<option value="${v.id}">v${v.version}</option>`;
                    });
                });
        }
    </script>
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