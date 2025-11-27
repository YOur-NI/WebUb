<?php

$books = [
    ['title' => '1984', 'author' => 'Оруэлл', 'year' => 1949],
    ['title' => 'Мастер и Маргарита', 'author' => 'Булгаков', 'year' => 1967],
    ['title' => 'Атлант расправил плечи', 'author' => 'Рэнд', 'year' => 1957],
    ['title' => 'Преступление и наказание', 'author' => 'Достоевский'],
    ['title' => 'Собачье сердце', 'author' => 'Булгаков', 'year' => 1925],
];


#1. Получение названий книг
function getBookTitles(array $books): array{
    $titles = [];
    foreach ($books as $book) {
        $titles[] = $book['title'];
    }
    return $titles;
}

#2. Проверка наличия книги по автору
function hasBookByAuthor(array $books, string $author): bool{
    $author = mb_strtolower($author);
    foreach ($books as $book) {
        if (mb_strtolower($book['author']) === $author) {
            return true;
        }
    }
    return false;
}

#3. Добавление года по умолчанию
function addDefaultYear(array $books, int $defaultYear = 2025): array{
    $res = [];
    foreach ($books as $book){
        $newBook = $book;
        if (!isset($book['year'])){
            $newBook['year'] = $defaultYear;
        }
        $res[] = $newBook;
    }
    return $res;
}


#4. Фильтрация по году выпуска
function filterBooksByYear(array $books, int $minYear): array{
    $filtered = [];
    foreach ($books as $book) {
        if (isset($book['year']) && $book['year'] > $minYear) {
            $filtered[] = $book;
        }
    }
    return $filtered;
}


#5. Преобразование книг в строковые описания
function mapBooksToPairs(array $books): array{
    $res = [];
    foreach ($books as $book) {
        $title = $book['title'];
        $author = $book['author'];
        if (!isset($book['year'])) {
            $year = 'неизвестен';
        } else {
            $year = $book['year'];
        }
        $res[] = "$title ($author, $year)";
    }
    return $res;
}

#6. Сортировка книг
function sortBooks(array $books): array {
    usort($books, function($a, $b) {
        if ($a['year'] == $b['year']) {
            return strcmp($a['title'], $b['title']);
        }
        return $a['year'] - $b['year'];
    });
    return $books;
}

#7. Группировка элементов
function groupBy(array $items, string $key): array {
    $res = [];
    foreach ($items as $item) {
        if (isset($item[$key])) {
            $value = $item[$key];
            if (!isset($res[$value])) {
                $res[$value] = [];
            }
            $res[$value][] = $item;
        }
    }
    return $res;
}

#8. Реализация стека
function stackPush(array &$stack, mixed $value): void {
    array_push($stack, $value);
}

function stackPop(array &$stack): mixed {
    if (empty($stack)) {
        return null;
    }
    return array_pop($stack);
}

#9. Реализация очереди
function queueEnqueue(array &$queue, mixed $value): void {
    array_push($queue, $value);
}

function queueDequeue(array &$queue): mixed {
    if (empty($queue)) {
        return null;
    }
    return array_shift($queue);
}


#10. Безопасное получение значения из массива
function safeGet(array $array, string|int $key, mixed $default = null): mixed {
    return $array[$key] ?? $default;
}


#11. Проверка, является ли массив ассоциативным
function isAssociative(array $array): bool {
    if (empty($array)) {
        return false;
    }

    $keys = array_keys($array);
    
    foreach ($keys as $key) {
        if (!is_int($key)) {
            return true;
        }
    }

    $expected = 0;
    foreach ($keys as $key) {
        if ($key !== $expected) {
            return true;
        }
        $expected++;
    }

    return false;
}
 
#////////////////////////////////////////////E/////

echo "1. Получение названий книг.<br>\n";

print_r(getBookTitles($books));


echo "<br>\n";
echo "<br>\n2. Проверка наличия книги по автору.<br>\n";

var_dump(hasBookByAuthor($books, 'оруэлл'));

echo "<br>\n";
echo "<br>\n3. Добавление года по умолчанию.<br>\n";

print_r(addDefaultYear($books));

echo "<br>\n";
echo "<br>\n4. Фильтрация по году выпуска.<br>\n";

print_r(filterBooksByYear($books, 1950));

echo "<br>\n";
echo "<br>\n5. Преобразование книг в строковые описания.<br>\n";

print_r(mapBooksToPairs($books));

echo "<br>\n";
echo "<br>\n6. Сортировка книг.<br>\n";

print_r(sortBooks($books));

echo "<br>\n";
echo "<br>\n7. Группировка элементов.<br>\n";
print_r(groupBy($books, 'author'));

echo "<br>\n";
echo "<br>\n8. Реализация стека.<br>\n";

// Создаем стек
$stack = [];
stackPush($stack, 'first');
stackPush($stack, 'second');
stackPush($stack, 'third');
echo "Стек после добавления элементов: ";
print_r($stack);
echo "Извлекаем элемент из стека: " . stackPop($stack) . "<br>\n";
echo "Стек после извлечения: ";
print_r($stack);


echo "<br>\n";
echo "<br>\n9. Реализация очереди.<br>\n";

// Создаем очередь
$queue = [];
queueEnqueue($queue, 'first');
queueEnqueue($queue, 'second');
queueEnqueue($queue, 'third');
echo "Очередь после добавления элементов: ";
print_r($queue);
echo "Извлекаем элемент из очереди: " . queueDequeue($queue) . "<br>\n";
echo "Очередь после извлечения: ";
print_r($queue);


echo "<br>\n";
echo "<br>\n10. Безопасное получение значения из массива.<br>\n";

$testArray = ['key1' => 'value1', 'key2' => 'value2'];
echo "Значение по ключу 'key1': " . safeGet($testArray, 'key1') . "<br>\n";
echo "Значение по ключу 'key3' (не существует): ";
var_dump(safeGet($testArray, 'key3', 'default_value'));


echo "<br>\n";
echo "<br>\n11. Проверка, является ли массив ассоциативным.<br>\n";

$assocArray = ['name' => 'John', 'age' => 30];
$indexedArray = [1, 2, 3];
$mixedArray = [0 => 'a', 2 => 'b', 'name' => 'John'];
$emptyArray = [];

echo "Ассоциативный массив ['name' => 'John', 'age' => 30]: ";
var_dump(isAssociative($assocArray));
echo "Индексированный массив [1, 2, 3]: ";
var_dump(isAssociative($indexedArray));
echo "Смешанный массив [0 => 'a', 2 => 'b', 'name' => 'John']: ";
var_dump(isAssociative($mixedArray));
echo "Пустой массив []: ";
var_dump(isAssociative($emptyArray));



?>