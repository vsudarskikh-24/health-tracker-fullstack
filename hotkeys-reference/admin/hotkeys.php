<?php
require_once '../config.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Добавление горячей клавиши
if (isset($_POST['add_hotkey'])) {
    $product_version_id = (int)$_POST['product_version_id'];
    $function_group_id = (int)$_POST['function_group_id'];
    $key_combination = trim($_POST['key_combination']);
    $action_description = trim($_POST['action_description']);
    $additional_info = trim($_POST['additional_info']);
    
    if (!empty($key_combination) && !empty($action_description)) {
        $stmt = $pdo->prepare("
            INSERT INTO hotkeys (product_version_id, function_group_id, key_combination, action_description, additional_info)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$product_version_id, $function_group_id, $key_combination, $action_description, $additional_info]);
        $success = "Горячая клавиша добавлена!";
    } else {
        $error = "Заполните обязательные поля!";
    }
}

// Удаление горячей клавиши
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM hotkeys WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Горячая клавиша удалена!";
}

// Редактирование горячей клавиши
if (isset($_POST['edit_hotkey'])) {
    $id = (int)$_POST['id'];
    $key_combination = trim($_POST['key_combination']);
    $action_description = trim($_POST['action_description']);
    $additional_info = trim($_POST['additional_info']);
    $function_group_id = (int)$_POST['function_group_id'];
    
    $stmt = $pdo->prepare("
        UPDATE hotkeys 
        SET key_combination = ?, action_description = ?, additional_info = ?, function_group_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$key_combination, $action_description, $additional_info, $function_group_id, $id]);
    $success = "Горячая клавиша обновлена!";
}

// Получение данных
$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$function_groups = $pdo->query("SELECT * FROM function_groups ORDER BY name")->fetchAll();

// Фильтрация
$filter_product = isset($_GET['product']) ? (int)$_GET['product'] : 0;
$filter_version = isset($_GET['version']) ? (int)$_GET['version'] : 0;

$sql = "
    SELECT h.*, p.name as product_name, pv.version, fg.name as group_name
    FROM hotkeys h
    JOIN product_versions pv ON h.product_version_id = pv.id
    JOIN products p ON pv.product_id = p.id
    JOIN function_groups fg ON h.function_group_id = fg.id
    WHERE 1=1
";

if ($filter_product) {
    $sql .= " AND p.id = " . $filter_product;
}
if ($filter_version) {
    $sql .= " AND pv.id = " . $filter_version;
}

$sql .= " ORDER BY p.name, pv.version, fg.name, h.key_combination";

$hotkeys = $pdo->query($sql)->fetchAll();

// Получение версий для выбранного продукта
$versions = [];
if ($filter_product) {
    $stmt = $pdo->prepare("SELECT * FROM product_versions WHERE product_id = ?");
    $stmt->execute([$filter_product]);
    $versions = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление горячими клавишами</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-nav {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .admin-nav a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin-right: 1rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: #5568d3;
        }
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filter-group {
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-add { background: #28a745; color: white; }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-delete { background: #dc3545; color: white; }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        .modal-content {
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 2% auto;
            padding: 2rem;
            border-radius: 12px;
        }
        .modal-close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1>⚙️ Админ-панель</h1>
                <nav>
                    <a href="../index.php">На сайт</a>
                    <a href="../logout.php">Выход</a>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="admin-nav">
                <a href="index.php">📊 Главная</a>
                <a href="products.php">📦 Программы</a>
                <a href="hotkeys.php" class="active">⌨️ Горячие клавиши</a>
                <a href="suggestions.php">💡 Предложения</a>
            </div>

            <h1>Управление горячими клавишами</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <!-- Фильтры -->
            <div class="filter-section">
                <h3>Фильтры</h3>
                <form method="GET" class="filter-group">
                    <div>
                        <label>Программа:</label>
                        <select name="product" id="productFilter" onchange="this.form.submit()">
                            <option value="">Все программы</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= $filter_product == $product['id'] ? 'selected' : '' ?>>
                                    <?= e($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($filter_product && !empty($versions)): ?>
                    <div>
                        <label>Версия:</label>
                        <select name="version" onchange="this.form.submit()">
                            <option value="">Все версии</option>
                            <?php foreach ($versions as $version): ?>
                                <option value="<?= $version['id'] ?>" <?= $filter_version == $version['id'] ? 'selected' : '' ?>>
                                    v<?= e($version['version']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <a href="hotkeys.php" class="btn" style="background: #dc3545; color: white;">Сбросить</a>
                </form>
            </div>

            <button class="btn btn-add" onclick="openModal('addHotkeyModal')" style="margin-bottom: 2rem;">➕ Добавить горячую клавишу</button>

            <!-- Таблица горячих клавиш -->
            <table class="hotkeys-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Программа</th>
                        <th>Версия</th>
                        <th>Группа</th>
                        <th>Клавиши</th>
                        <th>Действие</th>
                        <th>Популярность</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotkeys as $hotkey): ?>
                        <tr>
                            <td><?= $hotkey['id'] ?></td>
                            <td><?= e($hotkey['product_name']) ?></td>
                            <td>v<?= e($hotkey['version']) ?></td>
                            <td><?= e($hotkey['group_name']) ?></td>
                            <td><kbd><?= e($hotkey['key_combination']) ?></kbd></td>
                            <td><?= e($hotkey['action_description']) ?></td>
                            <td><?= $hotkey['popularity'] ?></td>
                            <td>
                                <button class="btn btn-edit" onclick='editHotkey(<?= json_encode($hotkey) ?>)'>✏️</button>
                                <a href="?delete=<?= $hotkey['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Модальное окно добавления -->
    <div id="addHotkeyModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('addHotkeyModal')">&times;</span>
            <h2>Добавить горячую клавишу</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Программа:</label>
                    <select name="product_id" id="addProductSelect" required onchange="loadVersionsForAdd()">
                        <option value="">Выберите программу</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= e($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Версия:</label>
                    <select name="product_version_id" id="addVersionSelect" required>
                        <option value="">Сначала выберите программу</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Группа функций:</label>
                    <select name="function_group_id" required>
                        <?php foreach ($function_groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Комбинация клавиш:</label>
                    <input type="text" name="key_combination" placeholder="Ctrl+C" required>
                </div>
                
                <div class="form-group">
                    <label>Описание действия:</label>
                    <textarea name="action_description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Дополнительная информация:</label>
                    <textarea name="additional_info"></textarea>
                </div>
                
                <button type="submit" name="add_hotkey" class="btn btn-add">Добавить</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div id="editHotkeyModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('editHotkeyModal')">&times;</span>
            <h2>Редактировать горячую клавишу</h2>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Группа функций:</label>
                    <select name="function_group_id" id="edit_function_group_id" required>
                        <?php foreach ($function_groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Комбинация клавиш:</label>
                    <input type="text" name="key_combination" id="edit_key_combination" required>
                </div>
                
                <div class="form-group">
                    <label>Описание действия:</label>
                    <textarea name="action_description" id="edit_action_description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Дополнительная информация:</label>
                    <textarea name="additional_info" id="edit_additional_info"></textarea>
                </div>
                
                <button type="submit" name="edit_hotkey" class="btn btn-edit">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        
        function loadVersionsForAdd() {
            const productId = document.getElementById('addProductSelect').value;
            const versionSelect = document.getElementById('addVersionSelect');
            
            if (!productId) {
                versionSelect.innerHTML = '<option value="">Сначала выберите программу</option>';
                return;
            }
            
            fetch(`../api.php?action=getVersions&product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    versionSelect.innerHTML = '<option value="">Выберите версию</option>';
                    data.forEach(v => {
                        versionSelect.innerHTML += `<option value="${v.id}">v${v.version}</option>`;
                    });
                });
        }
        
        function editHotkey(hotkey) {
            document.getElementById('edit_id').value = hotkey.id;
            document.getElementById('edit_key_combination').value = hotkey.key_combination;
            document.getElementById('edit_action_description').value = hotkey.action_description;
            document.getElementById('edit_additional_info').value = hotkey.additional_info || '';
            document.getElementById('edit_function_group_id').value = hotkey.function_group_id;
            openModal('editHotkeyModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш - Админ-панель</p>
        </div>
    </footer>
</body>
</html>