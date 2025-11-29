ИНСТРУКЦИЯ ПО ЗАПУСКУ ЧЕРЕЗ XAMPP

## **Шаг 1: Установка XAMPP**
1. Скачайте XAMPP: https://www.apachefriends.org/
2. Установите (по умолчанию в `C:\xampp`)

## **Шаг 2: Размещение файлов**
1. Откройте папку `C:\xampp\htdocs\`
2. Создайте папку `hotkeys-reference`
3. Скопируйте туда все файлы проекта:
```
C:\xampp\htdocs\hotkeys-reference\
├── config.php
├── database.sql
├── index.php
├── search.php
├── program.php
├── compare.php
├── api.php
├── register.php
├── login.php
├── logout.php
├── suggest.php
├── generate_pdf.php
├── admin/
│   └── index.php
├── css/
│   └── style.css
└── js/
    └── main.js
```

## **Шаг 3: Запуск XAMPP**
1. Откройте **XAMPP Control Panel**
2. Нажмите **Start** напротив **Apache**
3. Нажмите **Start** напротив **MySQL**

## **Шаг 4: Создание базы данных**
1. Откройте браузер
2. Перейдите: `http://localhost/phpmyadmin`
3. Нажмите вкладку **SQL**
4. Скопируйте ВЕСЬ код из файла `database.sql`
5. Вставьте в поле и нажмите **Выполнить**

## **Шаг 5: Открытие сайта**
Откройте в браузере:
```
http://localhost/hotkeys-reference/index.php