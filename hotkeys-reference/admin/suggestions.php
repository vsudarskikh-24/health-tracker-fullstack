<?php
require_once '../config.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$success = '';

// Одобрение предложения
if (isset($_POST['approve'])) {
    $id = (int)$_POST['id'];
    
    // Получаем данные предложения
    $stmt = $pdo->prepare("SELECT * FROM user_suggestions WHERE id = ?");
    $stmt->execute([$id]);
    $suggestion = $stmt->fetch();
    
    if ($suggestion) {
        // Получаем первую версию продукта
        $stmt = $pdo->prepare("SELECT id FROM product_versions WHERE product_id = ? ORDER BY release_date DESC LIMIT 1");
        $stmt->execute([$suggestion['product_id']]);
        $version = $stmt->fetch();
        
        if ($version) {
            // Добавляем в hotkeys (используем первую группу функций)
            $stmt = $pdo->prepare("
                INSERT INTO hotkeys (product_version_id, function_group_id, key_combination, action_description)
                VALUES (?, 1, ?, ?)
            ");
            $stmt->execute([$version['id'], $suggestion['key_combination'], $suggestion['action_description']]);
            
            // Обновляем статус
            $stmt = $pdo->prepare("UPDATE user_suggestions SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            
            $success = "Предложение одобрено и добавлено!";
        }
    }
}

// Отклонение предложения
if (isset($_POST['reject'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("UPDATE user_suggestions SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Предложение отклонено!";
}

// Удаление предложения
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM user_suggestions WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Предложение удалено!";
}

// Получение всех предложений
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

$sql = "
    SELECT s.*, u.username, p.name as product_name
    FROM user_suggestions s
    JOIN users u ON s.user_id = u.id
    JOIN products p ON s.product_id = p.id
";

if ($filter !== 'all') {
    $sql .= " WHERE s.status = '" . $pdo->quote($filter) . "'";
}

$sql .= " ORDER BY s.created_at DESC";

$suggestions = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предложения пользователей</title>
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
        .filter-tabs {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filter-tabs a {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            background: #e0e0e0;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
        }
        .filter-tabs a.active {
            background: #667eea;
            color: white;
        }
        .suggestion-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .suggestion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        .suggestion-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-delete { background: #6c757d; color: white; }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.85rem;
        }
        .status-pending { background: #ffc107; color: #333; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
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
                <a href="hotkeys.php">⌨️ Горячие клавиши</a>
                <a href="suggestions.php" class="active">💡 Предложения</a>
            </div>

            <h1>Предложения пользователей</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <div class="filter-tabs">
                <a href="?filter=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Ожидают модерации</a>
                <a href="?filter=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">Одобренные</a>
                <a href="?filter=rejected" class="<?= $filter === 'rejected' ? 'active' : '' ?>">Отклоненные</a>
                <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">Все</a>
            </div>

            <?php if (empty($suggestions)): ?>
                <p>Нет предложений в данной категории</p>
            <?php else: ?>
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="suggestion-card">
                        <div class="suggestion-header">
                            <div>
                                <h3><?= e($suggestion['product_name']) ?></h3>
                                <p>От: <strong><?= e($suggestion['username']) ?></strong></p>
                                <p>Дата: <?= date('d.m.Y H:i', strtotime($suggestion['created_at'])) ?></p>
                                <span class="status-badge status-<?= $suggestion['status'] ?>">
                                    <?= $suggestion['status'] === 'pending' ? 'Ожидает' : ($suggestion['status'] === 'approved' ? 'Одобрено' : 'Отклонено') ?>
                                </span>
                            </div>
                            <?php if ($suggestion['status'] === 'pending'): ?>
                            <div class="suggestion-actions">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $suggestion['id'] ?>">
                                    <button type="submit" name="approve" class="btn btn-approve">✅ Одобрить</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $suggestion['id'] ?>">
                                    <button type="submit" name="reject" class="btn btn-reject">❌ Отклонить</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <p><strong>Комбинация клавиш:</strong> <kbd><?= e($suggestion['key_combination']) ?></kbd></p>
                            <p><strong>Описание действия:</strong> <?= e($suggestion['action_description']) ?></p>
                        </div>
                        
                        <div style="margin-top: 1rem;">
                            <a href="?delete=<?= $suggestion['id'] ?>&filter=<?= $filter ?>" class="btn btn-delete" onclick="return confirm('Удалить предложение?')">🗑️ Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш - Админ-панель</p>
        </div>
    </footer>
</body>
</html>