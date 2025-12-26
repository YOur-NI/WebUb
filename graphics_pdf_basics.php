<?php

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!extension_loaded('gd')) {
    http_response_code(500);
    die('Расширение GD не загружено.');
}

require_once __DIR__ . '/FPDF/fpdf.php';

/**
 * Создаёт и выводит изображение 200×200 с белым фоном и чёрным заполненным квадратом 100×100 по центру.
 *
 * @return void
 */
function renderBlackSquare(): void
{
    $width = 200;
    $height = 200;
    $squareSize = 100;

    $image = imagecreatetruecolor($width, $height);
    if ($image === false) {
        http_response_code(500);
        die('Не удалось создать изображение.');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);

    $black = imagecolorallocate($image, 0, 0, 0);
    $x = ($width - $squareSize) / 2;
    $y = ($height - $squareSize) / 2;
    imagefilledrectangle($image, $x, $y, $x + $squareSize, $y + $squareSize, $black);

    header('Content-Type: image/png');
    imagepng($image);

    imagedestroy($image);
}

/**
 * Выводит текст встроенного шрифта №5 на изображении 300×100 в левом верхнем углу.
 *
 * @param string $text Текст для отображения (макс. 50 символов)
 * @return void
 */
function renderTextImage(string $text): void
{
    if (mb_strlen($text) > 50) {
        http_response_code(400);
        die('Текст слишком длинный (макс. 50 символов).');
    }

    $image = imagecreatetruecolor(300, 100);
    if ($image === false) {
        http_response_code(500);
        die('Не удалось создать изображение.');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefill($image, 0, 0, $white);

    // Готовый шрифт №5: 8x10
    imagestring($image, 5, 5, 5, $text, $black);

    header('Content-Type: image/png');
    imagepng($image);

    imagedestroy($image);
}

/**
 * Отображает текст с использованием TrueType-шрифта.
 *
 * @param string $text Текст для отображения
 * @param string $fontPath Путь к TTF-файлу
 * @return void
 */
function renderTtfText(string $text, string $fontPath): void
{
    if (!is_readable($fontPath)) {
        http_response_code(404);
        die('Шрифт не найден или недоступен для чтения.');
    }

    $image = imagecreatetruecolor(400, 100);
    if ($image === false) {
        http_response_code(500);
        die('Не удалось создать изображение.');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefill($image, 0, 0, $white);

    // Размер шрифта — 16 пикселей
    $fontSize = 16;
    $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
    if ($bbox === false) {
        http_response_code(500);
        die('Ошибка при определении габаритов текста.');
    }

    $x = 10;
    $y = 30 + abs($bbox[7]); // компенсация baseline

    $result = imagettftext($image, $fontSize, 0, $x, $y, $black, $fontPath, $text);
    if ($result === false) {
        http_response_code(500);
        die('Не удалось отрисовать текст с использованием TTF-шрифта.');
    }

    header('Content-Type: image/png');
    imagepng($image);

    imagedestroy($image);
}

/**
 * Накладывает текст по центру на фоновое изображение (например, кнопку).
 *
 * @param string $text Текст (только буквы, цифры, пробелы)
 * @param string $bgImagePath Путь к фоновому изображению
 * @return void
 */
function renderButton(string $text, string $bgImagePath): void
{
    if (preg_match('/^[а-яА-Яa-zA-Z0-9\s]*$/u', $text) !== 1) {
        http_response_code(400);
        die('Текст может содержать только буквы, цифры и пробелы.');
    }

    if (!is_readable($bgImagePath)) {
        http_response_code(404);
        die('Фоновое изображение не найдено.');
    }

    $bgInfo = getimagesize($bgImagePath);
    if ($bgInfo === false) {
        http_response_code(500);
        die('Не удалось определить размеры фонового изображения.');
    }

    $image = match ($bgInfo[2]) {
        IMAGETYPE_PNG => imagecreatefrompng($bgImagePath),
        IMAGETYPE_JPEG, IMAGETYPE_JPEG2000 => imagecreatefromjpeg($bgImagePath),
        IMAGETYPE_GIF => imagecreatefromgif($bgImagePath),
        default => null,
    };

    if ($image === false || $image === null) {
        http_response_code(500);
        die('Не удалось загрузить фоновое изображение.');
    }

    $width = imagesx($image);
    $height = imagesy($image);

    // Используем встроенный шрифт №5 для простоты центровки
    $fontWidth = imagefontwidth(5);
    $fontHeight = imagefontheight(5);
    $textWidth = strlen($text) * $fontWidth;
    $x = ($width - $textWidth) / 2;
    $y = ($height - $fontHeight) / 2;

    $black = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, $x, $y, $text, $black);

    header('Content-Type: image/png');
    imagepng($image);

    imagedestroy($image);
}

/**
 * Возвращает кэшированное изображение или генерирует новое и сохраняет в кэш.
 *
 * @param string $cacheDir Директория кэша
 * @param string $key Уникальный идентификатор для кэширования
 * @param callable $generator Функция, которая должна вывести изображение и сохранить его в файл
 * @return void
 */
function getCachedImageOrGenerate(string $cacheDir, string $key, callable $generator): void
{
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true)) {
            http_response_code(500);
            die('Не удалось создать директорию кэша.');
        }
    }

    $cacheFile = $cacheDir . '/' . md5($key) . '.png';

    if (is_readable($cacheFile)) {
        header('Content-Type: image/png');
        readfile($cacheFile);
        return;
    }

    // Перехватываем вывод генератора
    ob_start();
    $generator();
    $imageData = ob_get_contents();
    ob_end_clean();

    if ($imageData === false) {
        http_response_code(500);
        die('Ошибка при генерации изображения.');
    }

    // Сохраняем в кэш
    if (file_put_contents($cacheFile, $imageData) === false) {
        http_response_code(500);
        die('Не удалось сохранить изображение в кэш.');
    }

    header('Content-Type: image/png');
    echo $imageData;
}

/**
 * Генерирует простой PDF-документ с сообщением.
 *
 * @param string $message Текст сообщения
 * @return void
 */
function renderSimplePdf(string $message): void
{
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $message, 0, 1, 'C');
    $pdf->Output();
}

/**
 * Расширенный класс FPDF для генерации счетов.
 */
class InvoicePdf extends FPDF
{
    /**
     * Верхний колонтитул.
     *
     * @return void
     */
    public function Header(): void
    {
        $logoPath = __DIR__ . '/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 30);
        }

        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Счёт', 0, 0, 'C');
        $this->Ln(20);
    }

    /**
     * Нижний колонтитул.
     *
     * @return void
     */
    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Страница ' . $this->PageNo(), 0, 0, 'C');
    }

    /**
     * Рисует таблицу по переданным заголовкам и данным.
     *
     * @param array<int, string> $header Заголовки колонок
     * @param array<int, array<int, string>> $data Строки данных
     * @return void
     */
    public function buildTable(array $header, array $data): void
    {
        $colWidth = 40;
        $lineHeight = 8;

        // Заголовки
        $this->SetFont('Arial', 'B', 12);
        foreach ($header as $col) {
            $this->Cell($colWidth, $lineHeight, $col, 1, 0, 'C');
        }
        $this->Ln();

        // Данные
        $this->SetFont('Arial', '', 12);
        foreach ($data as $row) {
            foreach ($row as $col) {
                $this->Cell($colWidth, $lineHeight, $col, 1, 0, 'C');
            }
            $this->Ln();
        }
    }

    /**
     * Генерирует PDF-счёт с таблицей товаров.
     *
     * @param array<int, array<string, string|int>> $items Список товаров
     * @return void
     */
    public function renderInvoice(array $items): void
    {
        $this->AddPage();
        $header = ['Наименование', 'Кол-во', 'Цена', 'Сумма'];
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                (string) ($item['name'] ?? ''),
                (string) ($item['qty'] ?? ''),
                (string) ($item['price'] ?? ''),
                (string) ($item['total'] ?? ''),
            ];
        }
        $this->buildTable($header, $data);

        // Гиперссылка в конце
        $this->Ln(10);
        $this->SetFont('Arial', 'U', 12);
        $this->SetTextColor(0, 0, 255);
        $this->Write(10, 'Посетить сайт', 'https://example.com');
    }
}

/**
 * Генерирует персонализированный значок (badge).
 *
 * @param string $name Имя (только буквы и пробелы, 2–50 символов)
 * @return void
 */
function renderBadge(string $name): void
{
    $name = trim($name);
    if (
        mb_strlen($name) < 2 ||
        mb_strlen($name) > 50 ||
        preg_match('/^[а-яА-Яa-zA-Z\s]+$/u', $name) !== 1
    ) {
        http_response_code(400);
        die('Недопустимое имя: только буквы и пробелы, длина от 2 до 50 символов.');
    }

    $bgPath = __DIR__ . '/badge-bg.png';
    if (!is_readable($bgPath)) {
        http_response_code(500);
        die('Фон badge-bg.png не найден.');
    }

    $cacheDir = __DIR__ . '/cache/badge';

    getCachedImageOrGenerate($cacheDir, $name, function () use ($name, $bgPath) {
        $image = imagecreatefrompng($bgPath);
        if ($image === false) {
            http_response_code(500);
            die('Не удалось загрузить фон badge-bg.png.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $black = imagecolorallocate($image, 0, 0, 0);
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($name);
        $x = ($width - $textWidth) / 2;
        $y = ($height - imagefontheight($font)) / 2;

        imagestring($image, $font, $x, $y, $name, $black);

        imagepng($image);

        imagedestroy($image);
    });
}

/**
 * Генерирует PDF-счёт с предустановленными товарами.
 *
 * @return void
 */
function renderInvoicePdf(): void
{
    $items = [
        ['name' => 'Товар A', 'qty' => 2, 'price' => 100, 'total' => 200],
        ['name' => 'Товар B', 'qty' => 1, 'price' => 150, 'total' => 150],
        ['name' => 'Товар C', 'qty' => 3, 'price' => 50,  'total' => 150],
        ['name' => 'Товар D', 'qty' => 5, 'price' => 30,  'total' => 150],
        ['name' => 'Товар E', 'qty' => 2, 'price' => 80,  'total' => 160],
    ];

    $pdf = new InvoicePdf();
    $pdf->renderInvoice($items);
}

// Роутинг
$type = $_GET['type'] ?? null;
if ($type === 'badge') {
    // Валидация имени через renderBadge()
    renderBadge($_GET['name'] ?? '');
} elseif ($type === 'invoice') {
    renderInvoicePdf();
} else {
    // По умолчанию — можно вызвать пример из задания 1
    // renderBlackSquare(); // раскомментировать при необходимости теста
}

// ----------------------------------------------------------------------------
// Демонстрация всех функций (раскомментировать для тестирования):
//
// renderBlackSquare();
//renderTextImage("Привет, мир!");
//renderTtfText("Hello TTF", __DIR__ . '/arial.ttf'); // убедитесь, что шрифт существует
renderButton("Кнопка 123", __DIR__ . '/button-bg.png');
//
// renderSimplePdf("Тестовый PDF");
//
// $pdf = new InvoicePdf();
// $pdf->renderInvoice([
//     ['name' => 'Товар', 'qty' => 1, 'price' => 100, 'total' => 100]
// ]);
//
// renderBadge("Иван");
//
// getCachedImageOrGenerate(__DIR__ . '/cache/test', 'testkey', function () {
//     $img = imagecreatetruecolor(100, 50);
//     $white = imagecolorallocate($img, 255, 255, 255);
//     imagefill($img, 0, 0, $white);
//     imagepng($img);
//     imagedestroy($img);
// });
// ----------------------------------------------------------------------------