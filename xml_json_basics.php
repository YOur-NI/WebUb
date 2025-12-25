<?php

declare(strict_types=1);

/**
 * xml_json_basics.php — реализация работы с XML и JSON в PHP.
 * Соблюдение PSR-12, строгая типизация, обработка ошибок и безопасность.
 */

// Защита от XXE: отключаем загрузку внешних сущностей (актуально для PHP < 8.0)
if (PHP_VERSION_ID < 80000) {
    libxml_disable_entity_loader(true);
}
libxml_use_internal_errors(true);

/**
 * Задание 2. Парсинг XML через SimpleXML
 *
 * Загружает XML-файл и возвращает массив книг.
 *
 * @param string $filename Путь к XML-файлу
 * @return array Массив книг с ключами: isbn, title, authors
 * @throws RuntimeException При ошибке загрузки XML
 */
function loadBooksFromXml(string $filename): array
{
    $xml = simplexml_load_file($filename);
    if ($xml === false) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $message = 'Ошибка загрузки XML: ' . ($errors[0]->message ?? 'неизвестная ошибка');
        http_response_code(500);
        throw new RuntimeException($message, 500);
    }

    $books = [];
    foreach ($xml->book as $book) {
        $isbn = (string) $book['isbn'];
        $title = (string) $book->title;
        $authors = [];
        foreach ($book->authors->author as $author) {
            $authors[] = (string) $author;
        }
        $books[] = [
            'isbn' => $isbn,
            'title' => $title,
            'authors' => $authors,
        ];
    }
    return $books;
}

/**
 * Задание 3. Вывод книг в HTML-таблице
 *
 * Выводит HTML-таблицу с книгами.
 *
 * @param array $books Массив книг, как возвращаемый loadBooksFromXml()
 * @return void
 */
function renderBooksAsHtmlTable(array $books): void
{
    echo "<table border=\"1\">\n";
    echo "<thead><tr><th>ISBN</th><th>Название</th><th>Авторы</th></tr></thead>\n";
    echo "<tbody>\n";
    foreach ($books as $book) {
        $isbn = htmlspecialchars($book['isbn'], ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8');
        $authors = htmlspecialchars(implode(', ', $book['authors']), ENT_QUOTES, 'UTF-8');
        echo "<tr><td>$isbn</td><td>$title</td><td>$authors</td></tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
}

/**
 * Задание 4. Класс Book с JsonSerializable
 */
class Book implements JsonSerializable
{
    /**
     * Конструктор книги.
     *
     * @param string $isbn ISBN книги
     * @param string $title Название книги
     * @param array<string> $authors Список авторов
     */
    public function __construct(
        public string $isbn,
        public string $title,
        public array $authors
    ) {}

    /**
     * Реализация интерфейса JsonSerializable.
     *
     * @return array Данные для сериализации в JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'isbn' => $this->isbn,
            'title' => $this->title,
            'authors' => $this->authors,
        ];
    }
}

/**
 * Задание 6. Приём JSON от клиента
 *
 * Читает и парсит JSON из тела запроса.
 *
 * @return array|null Ассоциативный массив или null при ошибке
 */
function getJsonInput(): ?array
{
    $input = file_get_contents('php://input');
    if ($input === false) {
        http_response_code(400);
        return null;
    }
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        return null;
    }
    return $data;
}

/**
 * Задание 7. Приём XML от клиента
 *
 * Читает и парсит XML из тела запроса с защитой от XXE.
 *
 * @return SimpleXMLElement|null Объект XML или null при ошибке
 */
function getXmlInput(): ?SimpleXMLElement
{
    $input = file_get_contents('php://input');
    if ($input === false) {
        http_response_code(400);
        return null;
    }

    // SimpleXML не поддерживает флаги напрямую, поэтому используем libxml-настройки глобально
    $xml = simplexml_load_string($input);
    if ($xml === false) {
        libxml_clear_errors();
        http_response_code(400);
        return null;
    }
    return $xml;
}

/**
 * Задание 8. Преобразование XML в массив
 *
 * Рекурсивно преобразует SimpleXMLElement в ассоциативный массив.
 *
 * @param SimpleXMLElement $xml Корневой элемент XML
 * @return array Массив данных
 */
function xmlToArray(SimpleXMLElement $xml): array
{
    $array = [];
    foreach ($xml->children() as $key => $value) {
        if (count($value->children()) > 0) {
            $array[(string) $key] = xmlToArray($value);
        } else {
            $array[(string) $key] = (string) $value;
        }
    }
    return $array;
}

/**
 * Задание 5. API-эндпоинт /api/books.json
 */
if ($_SERVER['REQUEST_URI'] === '/api/books.json') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $books = loadBooksFromXml('books.xml');
        $bookObjects = array_map(
            fn(array $b): Book => new Book($b['isbn'], $b['title'], $b['authors']),
            $books
        );
        echo json_encode($bookObjects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

/**
 * Основной сценарий: отображение HTML-таблицы при прямом обращении к скрипту
 */
try {
    $books = loadBooksFromXml('books.xml');
    renderBooksAsHtmlTable($books);
} catch (RuntimeException $e) {
    http_response_code(500);
    echo 'Ошибка сервера: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit(1);
}

// ============================================================================
// Демонстрация всех функций (закомментировано)
// ============================================================================

// // Задание 2: Чтение XML через SimpleXML
// $books = loadBooksFromXml('books.xml');
// var_dump($books)."<br>\n";

// // Задание 3: Вывод книг в HTML-таблице
// renderBooksAsHtmlTable($books)."<br>\n";

// // Задание 4: Создание объектов Book с JsonSerializable
// $bookObjects = array_map(fn($b) => new Book($b['isbn'], $b['title'], $b['authors']), $books);
// echo json_encode($bookObjects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."<br>\n";

// // Задание 5: API-эндпоинт /api/books.json
// // Тест: curl http://localhost:8000/xml_json_basics.php/api/books.json

// // Задание 6: Приём JSON от клиента
// // Тест: curl -X POST -H "Content-Type: application/json" -d '{"title":"Тест"}' "http://localhost:8000/xml_json_basics.php?test=json"
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['test'] ?? null) === 'json') {
//     $input = getJsonInput();
//     var_dump($input)."<br>\n";
//     exit;
// }

// // Задание 7: Приём XML от клиента
// // Тест: curl -X POST -H "Content-Type: application/xml" -d '<book><title>Тест</title></book>' "http://localhost:8000/xml_json_basics.php?test=xml"
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['test'] ?? null) === 'xml') {
//     $input = getXmlInput();
//     var_dump($input)."<br>\n";
//     exit;
// }

// // Задание 8: Преобразование XML в массив
// $xml = simplexml_load_file('books.xml');
// if ($xml !== false) {
//     $array = xmlToArray($xml);
//     var_dump($array)."<br>\n";
// }
