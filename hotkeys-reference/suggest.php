<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $key_combination = trim($_POST['key_combination']);
    $action = trim($_POST['action']);
    
    if (!empty($key_combination) && !empty($action)) {
        $stmt = $pdo->prepare("
            INSERT INTO user_suggestions (user_id, product_id, key_combination, action_description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $product_id, $key_combination, $action]);
        $success = 'Спасибо! Ваше предложение отправлено на модерацию.';
    } else {
        $error = 'Заполните все поля';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предложить комбинацию</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1>⌨️ Справочник горячих клавиш</h1>
                <nav>
                    <a href="index.php">Главная</a>
                    <a href="logout.php">Выход</a>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Предложить свою комбинацию</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="suggestion-form">
                <div class="form-group">
                    <label>Программа:</label>
                    <select name="product_id" required>
                        <option value="">Выберите программу</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= e($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Горячие клавиши (например: Ctrl+Shift+S):</label>
                    <input type="text" name="key_combination" required placeholder="Ctrl+Shift+S">
                </div>
                
                <div class="form-group">
                    <label>Описание действия:</label>
                    <textarea name="action" required placeholder="Сохранить как..."></textarea>
                </div>
                
                <button type="submit">Отправить предложение</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Справочник горячих клавиш</p>
        </div>
    </footer>
</body>
</html>