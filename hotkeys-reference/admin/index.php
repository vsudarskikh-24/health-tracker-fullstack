<?php
require_once '../config.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Статистика
$stats = [];
$stats['products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stats['hotkeys'] = $pdo->query("SELECT COUNT(*) FROM hotkeys")->fetchColumn();
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['suggestions'] = $pdo->query("SELECT COUNT(*) FROM user_suggestions WHERE status = 'pending'")->fetchColumn();

// Последние добавленные горячие клавиши
$recent_hotkeys = $pdo->query("
    SELECT h.key_combination, h.action_description, p.name as product_name, h.created_at
    FROM hotkeys h
    JOIN product_versions pv ON h.product_version_id = pv.id
    JOIN products p ON pv.product_id = p.id
    ORDER BY h.created_at DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
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
        .admin-nav a:hover {
            background: #5568d3;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin-bottom: 1rem;
            color: #666;
        }
        .stat-card .number {
            font-size: 3rem;
            color: #667eea;
            font-weight: bold;
        }
        .recent-table {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
                <a href="hotkeys.php">⌨️ Горячие клавиши</a>
                <a href="suggestions.php">💡 Предложения (<?= $stats['suggestions'] ?>)</a>
            </div>

            <h1>Панель управления</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Программ</h3>
                    <div class="number"><?= $stats['products'] ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Горячих клавиш</h3>
                    <div class="number"><?= $stats['hotkeys'] ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Пользователей</h3>
                    <div class="number"><?= $stats['users'] ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Ожидающих предложений</h3>
                    <div class="number" style="color: #ffc107;"><?= $stats['suggestions'] ?></div>
                </div>
            </div>

            <div class="recent-table">
                <h2>Последние добавленные горячие клавиши</h2>
                <table class="hotkeys-table">
                    <thead>
                        <tr>
                            <th>Программа</th>
                            <th>Клавиши</th>
                            <th>Действие</th>
                            <th>Дата добавления</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_hotkeys as $hotkey): ?>
                            <tr>
                                <td><?= e($hotkey['product_name']) ?></td>
                                <td><kbd><?= e($hotkey['key_combination']) ?></kbd></td>
                                <td><?= e($hotkey['action_description']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($hotkey['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш - Админ-панель</p>
        </div>
    </footer>
</body>
</html>