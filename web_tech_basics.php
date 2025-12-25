<?php

declare(strict_types=1);

/**
 * Задание 5. Сессии: инициализация и использование
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Задание 1. Анализ HTTP-запроса
 */
function dumpRequestInfo(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Not provided';

    echo "<h2>HTTP Request Info</h2>\n";
    echo "<p><strong>Method:</strong> " . safeOutput($method) . "</p>\n";
    echo "<p><strong>URI:</strong> " . safeOutput($uri) . "</p>\n";

    if (!empty($_GET)) {
        echo "<p><strong>GET Parameters:</strong></p>\n<ul>\n";
        foreach ($_GET as $key => $value) {
            echo "<li>" . safeOutput($key) . " = " . safeOutput((string) $value) . "</li>\n";
        }
        echo "</ul>\n";
    }

    if (!empty($_POST)) {
        echo "<p><strong>POST Parameters:</strong></p>\n<ul>\n";
        foreach ($_POST as $key => $value) {
            echo "<li>" . safeOutput($key) . " = " . safeOutput((string) $value) . "</li>\n";
        }
        echo "</ul>\n";
    }

    echo "<p><strong>User-Agent:</strong> " . safeOutput($userAgent) . "</p>\n";
}

/**
 * Задание 2. Работа с суперглобальными массивами
 */
function getRequestData(): array
{
    return [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'get' => $_GET,
        'post' => $_POST,
        'server_info' => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? null,
            'HTTPS' => $_SERVER['HTTPS'] ?? null,
        ],
    ];
}

/**
 * Задание 3. Обработка GET- и POST-форм
 */
function renderForms(): void
{
    $search = $_GET['search'] ?? '';
    $message = $_POST['message'] ?? '';

    if ($search !== '') {
        echo "<p><strong>GET Search:</strong> " . safeOutput($search) . "</p>\n";
    }
    if ($message !== '') {
        echo "<p><strong>POST Message:</strong> " . safeOutput($message) . "</p>\n";
    }

    echo '<form method="GET">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" value="' . safeOutput($search) . '">
        <button type="submit">Search</button>
    </form><br><br>';

    echo '<form method="POST">
        <label for="message">Message:</label>
        <input type="text" id="message" name="message" value="' . safeOutput($message) . '">
        <button type="submit">Send</button>
    </form>';
}

/**
 * Задание 4. Cookies: установка и чтение
 */
function setThemeCookie(string $theme): void
{
    setcookie('theme', $theme, [
        'expires' => time() + 3600,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function getTheme(): string
{
    return $_COOKIE['theme'] ?? 'light';
}

/**
 * Задание 5. Сессии: инициализация и использование
 */
class SessionBag
{
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
}

/**
 * Задание 6. Безопасная валидация входных данных
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function safeOutput(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Задание 8. Защита от CSRF
 */
function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Задание 7. Защита от XSS (с CSRF из задания 8)
 */
function renderGuestbook(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo '<p style="color: red;">Ошибка CSRF-токена.</p>';
        } else {
            $comment = trim($_POST['comment']);
            if ($comment !== '') {
                $_SESSION['comments'] = $_SESSION['comments'] ?? [];
                $_SESSION['comments'][] = $comment;
            }
        }
    }

    $csrfToken = generateCsrfToken();
    echo '<form method="POST">
        <input type="hidden" name="csrf_token" value="' . safeOutput($csrfToken) . '">
        <label for="comment">Comment:</label><br>
        <textarea id="comment" name="comment">' . safeOutput($_POST['comment'] ?? '') . '</textarea><br>
        <button type="submit">Add Comment</button>
    </form><hr>';

    if (!empty($_SESSION['comments'])) {
        echo "<h3>Comments:</h3>\n<ul>\n";
        foreach ($_SESSION['comments'] as $comment) {
            echo "<li>" . safeOutput($comment) . "</li>\n";
        }
        echo "</ul>\n";
    }
}

/**
 * Задание 9. Регенерация ID сессии
 */
function rotateSessionId(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Задание 10. Корзина товаров на сессиях
 */
class ShoppingCart
{
    private const SESSION_KEY = 'cart';

    public function addItem(array $item): void
    {
        if (!isset($item['id']) || !isset($item['name']) || !isset($item['price'])) {
            throw new InvalidArgumentException('Item must contain id, name, and price.');
        }
        $_SESSION[self::SESSION_KEY] = $_SESSION[self::SESSION_KEY] ?? [];
        $_SESSION[self::SESSION_KEY][] = $item;
    }

    public function getItems(): array
    {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}

/**
 * Задание 11. Итоговое домашнее задание
 */
function handleLoginLogout(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $isLoggedIn = isset($_SESSION['user_id']);

    if (!$isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($email === '' || $password === '') {
            echo '<p style="color: red;">Заполните все поля.</p>';
        } elseif (!validateEmail($email)) {
            echo '<p style="color: red;">Неверный формат email.</p>';
        } elseif ($password !== 'secret') {
            echo '<p style="color: red;">Неверный пароль.</p>';
        } else {
            rotateSessionId();
            $_SESSION['user_id'] = 123;
            $_SESSION['email'] = $email;
            $isLoggedIn = true;
        }
    }

    if ($isLoggedIn) {
        echo '<p>Здравствуйте, ' . safeOutput($_SESSION['email']) . '</p>';
        echo '<form method="POST">';
        echo '<input type="hidden" name="action" value="logout">';
        echo '<button type="submit">Выход</button>';
        echo '</form>';
    } else {
        echo '<h2>Форма входа</h2>';
        echo '<form method="POST">';
        echo '<label>Email: <input type="email" name="email" value="' . safeOutput($email) . '" required></label><br>';
        echo '<label>Пароль: <input type="password" name="password" required></label><br>';
        echo '<button type="submit">Войти</button>';
        echo '</form>';
    }
}

// Инициализация сессии после объявления всех функций
initSession();


 //* Демонстрационный блок (закомментирован)
 
// Задание 1
echo "<h1>Задание 1: Анализ HTTP-запроса</h1>\n";
dumpRequestInfo();

// Задание 2
echo "<h1>Задание 2: Данные запроса</h1>\n";
echo "<pre>" . safeOutput(print_r(getRequestData(), true)) . "</pre>\n";

// Задание 3
echo "<h1>Задание 3: GET и POST формы</h1>\n";
renderForms();

// Задание 4
if (isset($_GET['theme'])) {
    setThemeCookie($_GET['theme']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
echo "<h1>Задание 4: Тема из cookie</h1>\n";
echo "<p>Текущая тема: <strong>" . safeOutput(getTheme()) . "</strong></p>\n";
echo '<p><a href="?theme=dark">Установить тему: dark</a> | <a href="?theme=light">Установить тему: light</a></p>';

// Задание 5
echo "<h1>Задание 5: SessionBag</h1>\n";
$bag = new SessionBag();
$bag->set('test_key', 'test_value');
echo "<p>SessionBag has 'test_key': " . ($bag->has('test_key') ? 'yes' : 'no') . "</p>\n";
echo "<p>SessionBag get 'test_key': " . safeOutput((string) $bag->get('test_key')) . "</p>\n";
$bag->remove('test_key');
echo "<p>After removal — has 'test_key': " . ($bag->has('test_key') ? 'yes' : 'no') . "</p>\n";

// Задание 6
echo "<h1>Задание 6: Валидация и экранирование</h1>\n";
$testEmail = 'user@example.com';
echo "<p>Email '" . safeOutput($testEmail) . "' валиден: " . (validateEmail($testEmail) ? 'да' : 'нет') . "</p>\n";
$unsafe = '<script>alert("XSS")</script>';
echo "<p>Безопасный вывод: " . safeOutput($unsafe) . "</p>\n";

// Задание 7–8
echo "<h1>Задание 7–8: Гостевая книга с CSRF-защитой</h1>\n";
renderGuestbook();

// Задание 9
echo "<h1>Задание 9: Регенерация ID сессии</h1>\n";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_login'])) {
    if ($_POST['password'] === 'password') {
        rotateSessionId();
        $_SESSION['user_id'] = 999;
        echo "<p>✅ Вход выполнен. ID сессии был обновлён.</p>\n";
    } else {
        echo "<p>❌ Неверный пароль.</p>\n";
    }
}
echo '<form method="POST">
    <label>Имитация входа (пароль: <code>password</code>):</label><br>
    <input type="password" name="password" required>
    <button type="submit" name="simulate_login" value="1">Войти</button>
</form>';

// Задание 10
echo "<h1>Задание 10: Корзина</h1>\n";
$cart = new ShoppingCart();
if (isset($_GET['add_item'])) {
    $cart->addItem(['id' => 1, 'name' => 'Книга "PHP и безопасность"', 'price' => 890]);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
echo "<p>Товаров в корзине: " . count($cart->getItems()) . "</p>\n";
if (!empty($cart->getItems())) {
    echo "<ul>\n";
    foreach ($cart->getItems() as $item) {
        echo "<li>" . safeOutput($item['name']) . " — " . safeOutput((string) $item['price']) . " ₽</li>\n";
    }
    echo "</ul>\n";
    if (isset($_GET['clear_cart'])) {
        $cart->clear();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
    echo '<p><a href="?clear_cart">Очистить корзину</a></p>';
}
echo '<p><a href="?add_item">➕ Добавить товар в корзину</a></p>';

// Задание 11
echo "<h1>Задание 11: Авторизация</h1>\n";
handleLoginLogout();
