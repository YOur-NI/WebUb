<?php

/**
 * SQL-скрипт для создания базы данных и таблицы books:
 *
 * CREATE DATABASE IF NOT EXISTS library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 * USE library;
 *
 * CREATE TABLE IF NOT EXISTS books (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   title VARCHAR(255) NOT NULL,
 *   author VARCHAR(255),
 *   isbn VARCHAR(20),
 *   pub_year INT,
 *   available TINYINT DEFAULT 1
 * ) ENGINE=InnoDB;
 */

declare(strict_types=1);

// Учётные данные подключения - в реальном проекте храните вне веб-доступа
$username = 'dbuser';
$password = '123234345'; // Укажите ваш пароль, если он есть
$host = 'localhost';
$dbname = 'library';

/**
 * Задание 2. Безопасное подключение через PDO
 * Создаёт и возвращает объект подключения к базе данных.
 *
 * @return PDO Объект подключения к БД
 */
function getPdoConnection(): PDO
{
    global $username, $password, $host, $dbname;

    // Режим: 'development' или 'production'
    $mode = 'development';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            $options
        );
        return $pdo;
    } catch (PDOException $e) {
        if ($mode === 'development') {
            die("Ошибка подключения к БД: " . htmlspecialchars($e->getMessage()));
        } else {
            // В продакшене - только логирование, без раскрытия деталей
            error_log("DB Connection Error: " . $e->getMessage());
            die("Сервис временно недоступен.");
        }
    }
}

/**
 * Задание 3. Вставка книги
 * Добавляет новую книгу в таблицу books и возвращает её ID.
 *
 * @param string $title Название книги
 * @param string $author Автор книги
 * @param string $isbn ISBN книги
 * @param int $year Год публикации
 * @return int ID вставленной книги
 */
function addBook(string $title, string $author, string $isbn, int $year): int
{
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, pub_year) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $author, $isbn, $year]);
    return (int) $pdo->lastInsertId();
}

/**
 * Задание 4. Поиск книг по автору
 * Возвращает все книги заданного автора.
 *
 * @param string $author Имя автора
 * @return array Массив ассоциативных массивов с данными книг
 */
function findBooksByAuthor(string $author): array
{
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare("SELECT * FROM books WHERE author = ?");
    $stmt->execute([$author]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Задание 5. Чтение всех доступных книг
 * Возвращает все книги, у которых available = 1.
 *
 * @return array Массив доступных книг
 */
function getAllAvailableBooks(): array
{
    $pdo = getPdoConnection();
    $stmt = $pdo->query("SELECT * FROM books WHERE available = 1");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Задание 6. Обновление доступности книги
 * Обновляет флаг доступности для книги по ID.
 *
 * @param int $bookId ID книги
 * @param bool $available Доступна ли книга (true/false)
 * @return void
 */
function setBookAvailability(int $bookId, bool $available): void
{
    $pdo = getPdoConnection();
    $stmt = $pdo->prepare("UPDATE books SET available = :available WHERE id = :id");
    $stmt->execute(['available' => (int) $available, 'id' => $bookId]);
}

/**
 * Задание 7. Транзакции
 * Перемещает "запасы" между двумя книгами (уменьшает у одной, увеличивает у другой).
 * Использует транзакцию для обеспечения целостности.
 *
 * @param int $fromId ID книги-источника
 * @param int $toId ID книги-получателя
 * @param int $amount Количество для перемещения
 * @return void
 * @throws Exception Если операция невозможна (например, недостаточно available)
 */
function transferStock(int $fromId, int $toId, int $amount): void
{
    if ($amount <= 0) {
        throw new InvalidArgumentException("Amount must be positive.");
    }

    $pdo = getPdoConnection();
    try {
        $pdo->beginTransaction();

        // Уменьшаем у источника
        $stmt1 = $pdo->prepare("UPDATE books SET available = available - ? WHERE id = ? AND available >= ?");
        $updated1 = $stmt1->execute([$amount, $fromId, $amount]);

        if ($stmt1->rowCount() === 0) {
            throw new Exception("Недостаточно книг для перемещения из источника.");
        }

        // Увеличиваем у получателя
        $stmt2 = $pdo->prepare("UPDATE books SET available = available + ? WHERE id = ?");
        $stmt2->execute([$amount, $toId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

// =============================================================================
// ДЕМОНСТРАЦИОННЫЙ БЛОК (закомментирован по требованию)
// =============================================================================


// Подготовка: очистим и добавим тестовые данные
$pdo = getPdoConnection();
$pdo->exec("DELETE FROM books");

$book1Id = addBook("1984", "Оруэлл", "978-5-17-084243-4", 1949);
$book2Id = addBook("Скотный двор", "Оруэлл", "978-5-17-012345-6", 1945);
$book3Id = addBook("Гарри Поттер", "Роулинг", "978-5-12345678-9", 1997);


$booksByOrwell = findBooksByAuthor("Оруэлл");
if (empty($booksByOrwell)) {
    echo "Нет книг автора «Оруэлл».\n";
} else {
    foreach ($booksByOrwell as $book) {
        echo htmlspecialchars($book['title']) . " (" . $book['pub_year'] . ")<br>\n";
    }
}


echo "<br>\n=== Задание 5. Чтение всех доступных книг ===<br>\n";
$available = getAllAvailableBooks();
if (empty($available)) {
    echo "Нет доступных книг.<br>\n";
} else {
    foreach ($available as $book) {
        $status = $book['available'] ? 'в наличии' : 'недоступна';
        echo htmlspecialchars($book['title']) . " - " . $status . "<br>\n";
    }
}


echo "<br>\n=== Задание 6. Обновление доступности книги ===<br>\n";
setBookAvailability($book3Id, false);
echo "Книга «" . htmlspecialchars("Гарри Поттер") . "» теперь недоступна.<br>\n";

// Проверим, что изменения применились
$updatedBook = findBooksByAuthor("Роулинг")[0] ?? null;
if ($updatedBook) {
    $status = $updatedBook['available'] ? 'в наличии' : 'недоступна';
    echo "Текущий статус: " . htmlspecialchars($updatedBook['title']) . " - " . $status . "<br>\n";
}


echo "<br>\n=== Задание 8. Проверка устойчивости к SQL-инъекциям ===<br>\n";
$maliciousInput = "' OR '1'='1";
$injectionResult = findBooksByAuthor($maliciousInput);
echo "Поиск по строке: " . htmlspecialchars($maliciousInput) . "<br>\n";
echo "Найдено книг: " . count($injectionResult) . "<br>\n";
if (empty($injectionResult)) {
    echo "✅ SQL-инъекция успешно заблокирована.<br>\n";
} else {
    echo "❌ Уязвимость обнаружена! Инъекция сработала.<br>\n";
}


echo "<br>\n=== Задание 7. Транзакции ===<br>\n";
// Установим достаточный остаток для демонстрации
$pdo->prepare("UPDATE books SET available = 5 WHERE id = ?")->execute([$book1Id]);

echo "Исходное состояние:<br>\n";
$stmt1 = $pdo->prepare("SELECT title, available FROM books WHERE id = ?");
$stmt1->execute([$book1Id]);
$book1 = $stmt1->fetch();
$stmt2 = $pdo->prepare("SELECT title, available FROM books WHERE id = ?");
$stmt2->execute([$book2Id]);
$book2 = $stmt2->fetch();
echo htmlspecialchars($book1['title']) . ": " . $book1['available'] . "<br>\n";
echo htmlspecialchars($book2['title']) . ": " . $book2['available'] . "<br>\n";

try {
    transferStock($book1Id, $book2Id, 2);
    echo "✅ Перемещение 2 экземпляров выполнено успешно.<br>\n";

    // Проверим итоговое состояние
    $final1 = $pdo->prepare("SELECT available FROM books WHERE id = ?")->fetchColumn();
    $final2 = $pdo->prepare("SELECT available FROM books WHERE id = ?")->fetchColumn();
    echo "Итоговое состояние:<br>\n";
    echo htmlspecialchars($book1['title']) . ": " . $final1 . "<br>\n";
    echo htmlspecialchars($book2['title']) . ": " . $final2 . "<br>\n";
} catch (Exception $e) {
    echo "❌ Ошибка транзакции: " . htmlspecialchars($e->getMessage()) . "\n";
}