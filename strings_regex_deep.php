<?php

declare(strict_types=1);

/**
 * Генерирует HTML-шаблон электронного письма с персонализированными данными.
 *
 * @param string $name Имя получателя
 * @param string $product Название продукта
 * @return string HTML-шаблон письма
 */
function generateEmailTemplate(string $name, string $product): string
{
    $name = trim($name);
    $product = trim($product);

    if ($name === '') {
        $name = 'Товарищ';
    }

    if ($product === '') {
        $product = 'товар';
    }

    $html = <<<HTML
<div style="font-family: Arial; padding: 20px; border: 1px solid #ccc;">
    <p><b>Уважаемый Товарищ {$name},</b></p>
    <p>Ваш заказ – одна (1) {$product} из цигейкового меха – прибыл на склад №7 и готов к выдаче.</p>
</div>
HTML;
    return $html;
}

/**
 * Генерирует HTML-шаблон электронного письма для анонимных получателей.
 *
 * @return string HTML-шаблон письма
 */
function generateEmailTemplateND(): string
{
    $html = <<<'HTML'
<div style="font-family: Arial; padding: 20px; border: 1px solid #ccc;">
    <p><b>Товарищам, ожидающим получения посылки.</b></p>
    <p>На склад №7 поступила партия грузов, подлежащих выдаче.</p>
</div>
HTML;
    return $html;
}

/**
 * Возвращает первый и последний символ строки.
 *
 * @param string $str Входная строка
 * @return array Массив с ключами 'first' и 'last'
 */
function getFirstAndLastChar(string $str): array
{
    if ($str === '') {
        return ['first' => '', 'last' => ''];
    }

    $first = mb_substr($str, 0, 1, 'UTF-8');
    $last = mb_substr($str, -1, 1, 'UTF-8');

    return [
        'first' => $first,
        'last' => $last
    ];
}

/**
 * Объединяет имя и фамилию в полное имя.
 *
 * @param string $first Имя
 * @param string $last Фамилия
 * @return string Полное имя
 */
function buildFullName(string $first, string $last): string
{
    $first = trim($first);
    $last = trim($last);

    $fullName = $first . ' ' . $last;

    return trim($fullName);
}

/**
 * Преобразует строку в формат заголовка (каждое слово с заглавной буквы).
 *
 * @param string $phrase Входная строка
 * @return string Преобразованная строка
 */
function toTitleCase(string $phrase): string
{
    if ($phrase === '') {
        return '';
    }

    // Разбиваем строку по пробельным символам, сохраняя только непустые части
    $words = preg_split('/\s+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY);

    if ($words === false) {
        return $phrase;
    }

    $result = [];
    foreach ($words as $word) {
        if ($word !== '') {
            $firstChar = mb_substr($word, 0, 1, 'UTF-8');
            $rest = mb_substr($word, 1, null, 'UTF-8');
            $result[] = mb_strtoupper($firstChar, 'UTF-8') . $rest;
        }
    }

    return implode(' ', $result);
}

/**
 * Извлекает имя файла из пути к файлу.
 *
 * @param string $path Путь к файлу
 * @return string Имя файла
 */
function extractFileName(string $path): string
{
    if ($path === '') {
        return '';
    }

    $lastSlash = strrpos($path, '/');
    if ($lastSlash === false) {
        return $path;
    }

    return substr($path, $lastSlash + 1);
}

/**
 * Преобразует массив тегов в строку CSV.
 *
 * @param array $tags Массив тегов
 * @return string Строка CSV
 */
function tagListToCSV(array $tags): string
{
    $cleanedTags = [];
    foreach ($tags as $tag) {
        if (is_string($tag)) {
            $cleanedTags[] = trim($tag);
        }
    }

    return implode(', ', $cleanedTags);
}

/**
 * Преобразует строку CSV в массив тегов.
 *
 * @param string $csv Строка CSV
 * @return array Массив тегов
 */
function csvToTagList(string $csv): array
{
    if ($csv === '') {
        return [];
    }

    $tags = explode(',', $csv);
    $result = [];
    foreach ($tags as $tag) {
        $result[] = trim($tag);
    }

    return $result;
}

/**
 * Экранирует строку для безопасного вывода в HTML.
 *
 * @param string $userInput Входная строка от пользователя
 * @return string Экранированная строка
 */
function safeEcho(string $userInput): string
{
    return htmlspecialchars($userInput, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Кодирует строку для использования в URL.
 *
 * @param string $query Строка запроса
 * @return string Закодированная строка
 */
function buildSearchUrl(string $query): string
{
    return rawurlencode($query);
}

/**
 * Проверяет, соответствует ли пароль требованиям безопасности.
 *
 * @param string $pass Пароль для проверки
 * @return bool true, если пароль соответствует требованиям, иначе false
 */
function validatePassword(string $pass): bool
{
    $pattern = '/^(?=.*[A-Z])(?=.*\d).{8,}$/u';
    return preg_match($pattern, $pass) === 1;
}

/**
 * Возвращает строку с результатом проверки пароля.
 *
 * @param bool $isValid Результат проверки пароля
 * @return string Строка с результатом
 */
function passwordResult(bool $isValid): string
{
    return $isValid ? "Пароль подходит" : "Пароль НЕ подходит";
}

/**
 * Извлекает все адреса электронной почты из текста.
 *
 * @param string $text Текст для поиска
 * @return array Массив адресов электронной почты
 */
function extractEmails(string $text): array
{
    $pattern = '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/ui';
    preg_match_all($pattern, $text, $matches);
    return $matches[0] ?? [];
}

/**
 * Заменяет все числа в тексте на многоточие.
 *
 * @param string $text Входной текст
 * @return string Обработанный текст
 */
function highlightNumbers(string $text): string
{
    $pattern = '/\d+/u';
    return preg_replace($pattern, '...', $text);
}

// Демонстрация вызова всех функций

echo "<br>\n";
echo "1) Создание строк.<br>\n";
echo "<br>\n";

$n = 'Ivan';
$pr = 'Ushanka';
echo generateEmailTemplate($n, $pr) . "<br>\n";

echo generateEmailTemplateND() . "<br>\n";

echo "<br>\n";
echo "2) Длина и доступ к символам.<br>\n";
echo "<br>\n";

$s = 'Барашек говорит же';
$ch = getFirstAndLastChar($s);
echo $s . ' - ' . $ch['first'] . $ch['last'] . "<br>\n";

echo "<br>\n";
echo "3) Конкатенация и очистка строк.<br>\n";
echo "<br>\n";

$fname = '  Ник   ';
$sname = ' Суд  ';
echo buildFullName($fname, $sname) . "<br>\n";

echo "<br>\n";
echo "4) Изменение регистра.<br>\n";
echo "<br>\n";

echo toTitleCase("привет мир") . "<br>\n";
echo toTitleCase("hello world") . "<br>\n";
echo toTitleCase("ééé éé éééé") . "<br>\n";

echo "<br>\n";
echo "5) Поиск и извлечение подстрок.<br>\n";
echo "<br>\n";

echo extractFileName('/var/www/index.php') . "<br>\n";

echo "<br>\n";
echo "6) Разбиение и сборка строк.<br>\n";
echo "<br>\n";

$arrtag = ["php", "regex", "web"];
echo tagListToCSV($arrtag) . "<br>\n";
var_dump(csvToTagList(tagListToCSV($arrtag)));

echo "<br>\n";
echo "<br>\n";
echo "7) Экранирование для HTML.<br>\n";
echo "<br>\n";

$userInput = '<script>alert("XSS")</script>';
echo safeEcho($userInput) . "<br>\n";

echo "<br>\n";
echo "8) Кодирование для URL.<br>\n";
echo "<br>\n";

$url = "https://example.com/search?q=123";
echo buildSearchUrl($url) . "<br>\n";

echo "<br>\n";
echo "9) Регулярные выражения: валидация пароля.<br>\n";
echo "<br>\n";

$pswrd = 'Qwer1234';
$res = validatePassword($pswrd);
echo passwordResult($res) . "<br>\n";

echo "<br>\n";
echo "10) Регулярные выражения: извлечение данных.<br>\n";
echo "<br>\n";

$text = "Контакты: user@example.com, aaaa@11.2, admin@site.ru, test.email+tag@sub.domain.com";
$emails = extractEmails($text);
foreach ($emails as $email) {
    echo $email . "<br>\n";
}

echo "<br>\n";
echo "11) Регулярные выражения: замена.<br>\n";
echo "<br>\n";

$text = "qwwe123rr rr2r22r 3r3";
echo highlightNumbers($text) . "<br>\n";

?>