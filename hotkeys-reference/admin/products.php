<?php
require_once '../config.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Добавление продукта
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $category]);
        $success = "Программа '$name' успешно добавлена!";
    } else {
        $error = "Название программы обязательно!";
    }
}

// Удаление продукта
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Программа удалена!";
}

// Редактирование продукта
if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, category = ? WHERE id = ?");
    $stmt->execute([$name, $description, $category, $id]);
    $success = "Программа обновлена!";
}

// Добавление версии
if (isset($_POST['add_version'])) {
    $product_id = (int)$_POST['product_id'];
    $version = trim($_POST['version']);
    $release_date = $_POST['release_date'];
    
    $stmt = $pdo->prepare("INSERT INTO product_versions (product_id, version, release_date) VALUES (?, ?, ?)");
    $stmt->execute([$product_id, $version, $release_date]);
    $success = "Версия добавлена!";
}

// Получение всех продуктов
$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();

// Получение версий для каждого продукта
$versions = [];
foreach ($products as $product) {
    $stmt = $pdo->prepare("SELECT * FROM product_versions WHERE product_id = ? ORDER BY release_date DESC");
    $stmt->execute([$product['id']]);
    $versions[$product['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление программами</title>
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
        .product-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-add { background: #28a745; color: white; }
        .versions-list {
            margin-top: 1rem;
        }
        .version-item {
            padding: 0.5rem;
            background: #f9f9f9;
            margin-bottom: 0.5rem;
            border-radius: 6px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 5% auto;
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
                <a href="products.php" class="active">📦 Программы</a>
                <a href="hotkeys.php">⌨️ Горячие клавиши</a>
                <a href="suggestions.php">💡 Предложения</a>
            </div>

            <h1>Управление программами</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <button class="btn btn-add" onclick="openModal('addProductModal')" style="margin-bottom: 2rem;">➕ Добавить программу</button>

            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-header">
                        <div>
                            <h2><?= e($product['name']) ?></h2>
                            <p><?= e($product['description']) ?></p>
                            <span class="category-badge"><?= e($product['category']) ?></span>
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-edit" onclick="editProduct(<?= $product['id'] ?>, '<?= e($product['name']) ?>', '<?= e($product['description']) ?>', '<?= e($product['category']) ?>')">✏️ Редактировать</button>
                            <a href="?delete=<?= $product['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить программу?')">🗑️ Удалить</a>
                        </div>
                    </div>

                    <div class="versions-list">
                        <h3>Версии:</h3>
                        <?php if (!empty($versions[$product['id']])): ?>
                            <?php foreach ($versions[$product['id']] as $version): ?>
                                <div class="version-item">
                                    <strong>v<?= e($version['version']) ?></strong> - 
                                    <?= $version['release_date'] ? date('d.m.Y', strtotime($version['release_date'])) : 'Дата не указана' ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Версии не добавлены</p>
                        <?php endif; ?>
                        <button class="btn btn-add" onclick="openAddVersion(<?= $product['id'] ?>, '<?= e($product['name']) ?>')">➕ Добавить версию</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Модальное окно добавления продукта -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('addProductModal')">&times;</span>
            <h2>Добавить программу</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Название:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Описание:</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Категория:</label>
                    <input type="text" name="category" placeholder="IDE, Графика, Офис...">
                </div>
                <button type="submit" name="add_product" class="btn btn-add">Добавить</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования продукта -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('editProductModal')">&times;</span>
            <h2>Редактировать программу</h2>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Название:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Описание:</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Категория:</label>
                    <input type="text" name="category" id="edit_category">
                </div>
                <button type="submit" name="edit_product" class="btn btn-edit">Сохранить</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно добавления версии -->
    <div id="addVersionModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('addVersionModal')">&times;</span>
            <h2>Добавить версию для <span id="version_product_name"></span></h2>
            <form method="POST">
                <input type="hidden" name="product_id" id="version_product_id">
                <div class="form-group">
                    <label>Версия:</label>
                    <input type="text" name="version" placeholder="1.0, 2023, CC 2024..." required>
                </div>
                <div class="form-group">
                    <label>Дата релиза:</label>
                    <input type="date" name="release_date">
                </div>
                <button type="submit" name="add_version" class="btn btn-add">Добавить версию</button>
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
        
        function editProduct(id, name, description, category) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_category').value = category;
            openModal('editProductModal');
        }
        
        function openAddVersion(productId, productName) {
            document.getElementById('version_product_id').value = productId;
            document.getElementById('version_product_name').textContent = productName;
            openModal('addVersionModal');
        }

        // Закрытие модального окна при клике вне его
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