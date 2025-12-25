<?php

declare(strict_types=1);

/**
 * Задание 1. Валидация email
 *
 * @param string $email
 * @return string|null
 */
function validateEmail(string $email): ?string
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
        return $email;
    }
    return null;
}

/**
 * Задание 2. Валидация имени
 *
 * @param string $name
 * @return string|null
 */
function validateName(string $name): ?string
{
    if (preg_match('/^[A-Za-zА-Яа-я\s]{2,50}$/u', $name)) {
        return $name;
    }
    return null;
}

/**
 * Задание 3. Валидация возраста
 *
 * @param int $age
 * @return int|null
 */
function validateAge(int $age): ?int
{
    $options = ['options' => ['min_range' => 1, 'max_range' => 120]];
    if (filter_var($age, FILTER_VALIDATE_INT, $options) !== false) {
        return $age;
    }
    return null;
}

/**
 * Задание 4. Безопасное экранирование HTML
 *
 * @param string $text
 * @return string
 */
function escapeHtml(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Задание 5. CSRF-токен: генерация
 *
 * @return string
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Задание 5. CSRF-токен: проверка
 *
 * @param string $token
 * @return bool
 */
function validateCsrfToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Задание 6. Проверка MIME-типа файла
 *
 * @param string $tmpPath
 * @return bool
 */
function isValidImageFile(string $tmpPath): bool
{
    if (!is_file($tmpPath)) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        return false;
    }

    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    return in_array($mimeType, ['image/jpeg', 'image/png'], true);
}

/**
 * Задание 7. Генерация безопасного имени файла
 *
 * @param string $originalName
 * @return string
 */
function generateSafeFileName(string $originalName): string
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(16)) . '.' . $extension;
}

/**
 * Задание 8. Ограничение размера файла
 *
 * @param int $size
 * @param int $maxBytes
 * @return bool
 */
function isFileSizeValid(int $size, int $maxBytes = 1048576): bool // 1 MB = 1024*1024
{
    return $size > 0 && $size <= $maxBytes;
}

/**
 * Задание 9. Безопасное сохранение файла
 *
 * @param array $file
 * @param string $uploadDir
 * @return string|null
 */
function saveUploadedFile(array $file, string $uploadDir): ?string
{
    // Проверка на ошибку загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Проверка, что файл действительно загружен
    if (!is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    // Проверка размера
    if (!isFileSizeValid($file['size'])) {
        return null;
    }

    // Проверка MIME-типа
    if (!isValidImageFile($file['tmp_name'])) {
        return null;
    }

    // Генерация безопасного имени
    $safeName = generateSafeFileName($file['name']);
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

    // Убедимся, что директория существует
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return null;
        }
    }

    // Перемещение файла
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $safeName;
    }

    return null;
}

/**
 * Задание 10. Безопасные cookie и сессия
 *
 * @return void
 */
function secureSessionStart(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Установка безопасных параметров сессии
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

// ------------------------------------------------------------------------------------------------
// Задание 11. Итоговое домашнее задание — полный сценарий формы
// ------------------------------------------------------------------------------------------------

secureSessionStart();

$csrfToken = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $csrfToken = generateCsrfToken();
}

// Инициализация sticky-данных и ошибок
$emailInput = $_POST['email'] ?? '';
$nameInput = $_POST['name'] ?? '';
$ageInput = $_POST['age'] ?? '';
$errors = [];

$avatarPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF проверка
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('CSRF-атака!');
    }

    // Валидация email
    $email = validateEmail($emailInput);
    if ($email === null) {
        $errors['email'] = 'Некорректный email';
    }

    // Валидация имени
    $name = validateName($nameInput);
    if ($name === null) {
        $errors['name'] = 'Имя должно содержать только буквы и пробелы (2–50 символов)';
    }

    // Валидация возраста
    $age = null;
    if (is_numeric($ageInput)) {
        $ageInt = (int) $ageInput;
        $age = validateAge($ageInt);
    }
    if ($age === null) {
        $errors['age'] = 'Возраст должен быть целым числом от 1 до 120';
    }

    // Обработка файла
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = __DIR__ . '/../uploads';
        $avatar = saveUploadedFile($_FILES['avatar'], $uploadDir);
        if ($avatar === null) {
            $errors['avatar'] = 'Недопустимый файл аватара (только JPEG/PNG, до 1 МБ)';
        }
    }

    // Если нет ошибок — вывод результата
    if (empty($errors)) {
        $avatarPath = $avatar ? '../uploads/' . $avatar : null;
        echo '<h2>Данные успешно приняты:</h2>';
        echo '<p>Email: ' . escapeHtml($email) . '</p>';
        echo '<p>Имя: ' . escapeHtml($name) . '</p>';
        echo '<p>Возраст: ' . escapeHtml((string) $age) . '</p>';
        if ($avatarPath) {
            echo '<p>Аватар:<br><img src="' . escapeHtml($avatarPath) . '" alt="Аватар" width="100"></p>';
        }
        exit;
    }
}

// Отображение формы (включая sticky-данные и ошибки)
echo '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Безопасная форма</title>
</head>
<body>
    <h1>Регистрация</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="' . escapeHtml($_SESSION['csrf_token'] ?? '') . '">

        <label>Email:<br>
            <input type="email" name="email" value="' . escapeHtml($emailInput) . '" required>
        </label>
        ' . (isset($errors['email']) ? '<p style="color:red;">' . escapeHtml($errors['email']) . '</p>' : '') . '

        <label><br/n>Имя:<br>
            <input type="text" name="name" value="' . escapeHtml($nameInput) . '" required>
        </label>
        ' . (isset($errors['name']) ? '<p style="color:red;">' . escapeHtml($errors['name']) . '</p>' : '') . '

        <label><br/n>Возраст:<br>
            <input type="number" name="age" value="' . escapeHtml($ageInput) . '" min="1" max="120" required>
        </label>
        ' . (isset($errors['age']) ? '<p style="color:red;">' . escapeHtml($errors['age']) . '</p>' : '') . '

        <label><br/n>Аватар (необязательно):<br>
            <input type="file" name="avatar" accept="image/jpeg,image/png">
        </label>
        ' . (isset($errors['avatar']) ? '<p style="color:red;">' . escapeHtml($errors['avatar']) . '</p>' : '') . '

        <br><br>
        <button type="submit">Отправить</button>
    </form>
</body>
</html>';