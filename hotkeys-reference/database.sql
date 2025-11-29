-- Создание базы данных
CREATE DATABASE IF NOT EXISTS hotkeys_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotkeys_db;

-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица программных продуктов
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица версий программ
CREATE TABLE product_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    version VARCHAR(50) NOT NULL,
    release_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица групп функций
CREATE TABLE function_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица горячих клавиш
CREATE TABLE hotkeys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_version_id INT NOT NULL,
    function_group_id INT NOT NULL,
    keys VARCHAR(100) NOT NULL,
    action_description TEXT NOT NULL,
    additional_info TEXT,
    popularity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_version_id) REFERENCES product_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (function_group_id) REFERENCES function_groups(id) ON DELETE CASCADE,
    INDEX idx_keys (keys),
    INDEX idx_product_version (product_version_id),
    INDEX idx_function_group (function_group_id),
    INDEX idx_popularity (popularity),
    FULLTEXT idx_action (action_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица предложений пользователей
CREATE TABLE user_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    keys VARCHAR(100) NOT NULL,
    action_description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица истории изменений
CREATE TABLE hotkey_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotkey_id INT NOT NULL,
    old_keys VARCHAR(100),
    new_keys VARCHAR(100),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotkey_id) REFERENCES hotkeys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка тестовых данных
INSERT INTO users (username, email, password, is_admin) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
-- Пароль: password

INSERT INTO products (name, description, category) VALUES 
('Visual Studio Code', 'Редактор кода от Microsoft', 'IDE'),
('Adobe Photoshop', 'Графический редактор', 'Графика'),
('Microsoft Word', 'Текстовый редактор', 'Офис'),
('Google Chrome', 'Веб-браузер', 'Браузер');

INSERT INTO product_versions (product_id, version, release_date) VALUES 
(1, '1.75', '2023-01-01'),
(1, '1.80', '2023-06-01'),
(2, 'CC 2023', '2023-01-01'),
(3, '2021', '2021-01-01'),
(4, '120', '2023-11-01');

INSERT INTO function_groups (name, description) VALUES 
('Работа с текстом', 'Функции редактирования текста'),
('Навигация', 'Функции перемещения по документу'),
('Форматирование', 'Функции форматирования'),
('Разработка', 'Функции для разработки кода'),
('Графика', 'Функции работы с графикой'),
('Офис', 'Офисные функции');

INSERT INTO hotkeys (product_version_id, function_group_id, keys, action_description, popularity) VALUES 
(1, 1, 'Ctrl+C', 'Копировать выделенный текст', 100),
(1, 1, 'Ctrl+V', 'Вставить скопированный текст', 100),
(1, 1, 'Ctrl+X', 'Вырезать выделенный текст', 90),
(1, 1, 'Ctrl+Z', 'Отменить последнее действие', 95),
(1, 1, 'Ctrl+Y', 'Повторить отмененное действие', 85),
(1, 4, 'Ctrl+Shift+P', 'Открыть палитру команд', 80),
(1, 4, 'Ctrl+P', 'Быстрый переход к файлу', 90),
(1, 4, 'Ctrl+/', 'Закомментировать/раскомментировать строку', 85),
(1, 2, 'Ctrl+G', 'Перейти к строке', 70),
(1, 2, 'Ctrl+Home', 'Перейти к началу документа', 60),
(3, 5, 'Ctrl+T', 'Свободная трансформация', 75),
(3, 5, 'Ctrl+J', 'Дублировать слой', 70),
(4, 3, 'Ctrl+B', 'Жирный шрифт', 80),
(4, 3, 'Ctrl+I', 'Курсив', 75),
(4, 3, 'Ctrl+U', 'Подчеркнутый текст', 70);