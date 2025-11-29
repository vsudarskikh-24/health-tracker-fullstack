<?php
require_once 'config.php';

// Получение популярных комбинаций
$stmt = $pdo->query("
    SELECT h.key_combination, h.action_description, h.popularity, p.name as product_name, pv.version
    FROM hotkeys h
    JOIN product_versions pv ON h.product_version_id = pv.id
    JOIN products p ON pv.product_id = p.id
    ORDER BY h.popularity DESC
    LIMIT 10
");
$popular = $stmt->fetchAll();

// Получение всех программ для фильтров
$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$function_groups = $pdo->query("SELECT * FROM function_groups ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Справочник горячих клавиш</title>
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
                    <?php if (isLoggedIn()): ?>
                        <a href="suggest.php">Предложить комбинацию</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php">Админ-панель</a>
                        <?php endif; ?>
                        <a href="logout.php">Выход (<?= e($_SESSION['username']) ?>)</a>
                    <?php else: ?>
                        <a href="login.php">Вход</a>
                        <a href="register.php">Регистрация</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Главная строка поиска -->
            <section class="search-section">
                <div class="search-box">
                    <input type="text" id="mainSearch" placeholder="Найти горячую клавишу (например: Ctrl+C или 'копировать')..." autocomplete="off">
                    <button id="searchBtn">🔍 Поиск</button>
                </div>
                <div id="searchResults" class="search-results"></div>
            </section>

            <!-- Фильтры -->
            <section class="filters">
                <h2>Фильтры</h2>
                <div class="filter-group">
                    <label for="productFilter">Программа:</label>
                    <select id="productFilter">
                        <option value="">Все программы</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= e($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="versionFilter">Версия:</label>
                    <select id="versionFilter">
                        <option value="">Все версии</option>
                    </select>

                    <label for="groupFilter">Группа функций:</label>
                    <select id="groupFilter">
                        <option value="">Все группы</option>
                        <?php foreach ($function_groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button id="applyFilters">Применить фильтры</button>
                    <button id="resetFilters">Сбросить</button>
                    <button id="generatePDF">📄 Сгенерировать PDF-шпаргалку</button>
                </div>
            </section>

            <!-- Популярные комбинации -->
            <section class="popular-section">
                <h2>🔥 Популярные горячие клавиши</h2>
                <div class="hotkeys-grid">
                    <?php foreach ($popular as $hotkey): ?>
                        <div class="hotkey-card">
                            <div class="hotkey-keys">
                                <?= formatKeys($hotkey['key_combination']) ?>
                            </div>
                            <div class="hotkey-action"><?= e($hotkey['action_description']) ?></div>
                            <div class="hotkey-meta">
                                <?= e($hotkey['product_name']) ?> (v<?= e($hotkey['version']) ?>)
                                <span class="popularity">👁️ <?= $hotkey['popularity'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Результаты фильтрации -->
            <section id="filteredResults" class="filtered-results"></section>

            <!-- Список программ -->
            <section class="programs-section">
                <h2>📚 Доступные программы</h2>
                <div class="programs-grid">
                    <?php foreach ($products as $product): ?>
                        <a href="program.php?id=<?= $product['id'] ?>" class="program-card">
                            <h3><?= e($product['name']) ?></h3>
                            <p><?= e($product['description']) ?></p>
                            <span class="category-badge"><?= e($product['category']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш. Все права защищены.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>

<?php
// Функция форматирования клавиш
function formatKeys($keys) {
    $parts = explode('+', $keys);
    $formatted = '';
    foreach ($parts as $part) {
        $formatted .= '<kbd>' . e($part) . '</kbd>';
        if ($part !== end($parts)) {
            $formatted .= '<span class="plus">+</span>';
        }
    }
    return $formatted;
}
?>