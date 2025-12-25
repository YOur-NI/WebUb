<?php

header('Content-Type: text/html; charset=utf-8');

// ——————————————————————————————————————————————
// Подключение FPDF (если не установлен через composer)
// ——————————————————————————————————————————————
if (!class_exists('FPDF')) {
    if (file_exists(__DIR__ . '/FPDF/fpdf.php')) {
        require_once __DIR__ . '/FPDF/fpdf.php';
    } else {
        die('Ошибка: FPDF не найден. Скачайте fpdf.php в корень проекта.');
    }
}

// ——————————————————————————————————————————————
// Проверка расширения GD
// ——————————————————————————————————————————————
if (!extension_loaded('gd')) {
    die('Ошибка: расширение GD не загружено');
}

// ——————————————————————————————————————————————
// 1. Чёрный квадрат на белом фоне
// ——————————————————————————————————————————————
function renderBlackSquare(): void
{
    $size = 200;
    $rectSize = 100;
    $offset = ($size - $rectSize) / 2;

    $image = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefilledrectangle($image, 0, 0, $size - 1, $size - 1, $white);
    imagefilledrectangle($image, $offset, $offset, $offset + $rectSize - 1, $offset + $rectSize - 1, $black);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 2. Текст с встроенным шрифтом
// ——————————————————————————————————————————————
function renderTextImage(string $text): void
{
    if (strlen($text) > 50) {
        http_response_code(400);
        exit('Текст слишком длинный (макс. 50 ASCII-символов)');
    }

    $image = imagecreatetruecolor(300, 100);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefilledrectangle($image, 0, 0, 299, 99, $white);
    imagestring($image, 5, 0, 0, $text, $black);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 3. TrueType-шрифты
// ——————————————————————————————————————————————
function renderTtfText(string $text, string $fontPath): void
{
    if (!is_readable($fontPath)) {
        // Возвращаем ошибку как PNG
        $image = imagecreatetruecolor(400, 50);
        $red = imagecolorallocate($image, 255, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 399, 49, $white);
        imagestring($image, 2, 10, 15, "Ошибка: шрифт не найден", $red);
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        return;
    }

    $image = imagecreatetruecolor(400, 100);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, 399, 99, $white);

    // Размер шрифта ~20px
    imagettftext($image, 20, 0, 10, 40, $black, $fontPath, $text);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 4. Динамическая кнопка
// ——————————————————————————————————————————————
function renderButton(string $text, string $bgImagePath): void
{
    if (!preg_match('/^[a-zA-Z0-9\sа-яА-ЯёЁ]{1,50}$/u', $text)) {
        http_response_code(400);
        exit('Текст содержит запрещённые символы');
    }

    if (!is_readable($bgImagePath)) {
        http_response_code(404);
        exit('Фоновое изображение не найдено');
    }

    $bg = @imagecreatefrompng($bgImagePath);
    if (!$bg) {
        http_response_code(500);
        exit('Не удалось загрузить фон');
    }

    $width = imagesx($bg);
    $height = imagesy($bg);
    $image = imagecreatetruecolor($width, $height);
    imagecopy($image, $bg, 0, 0, 0, 0, $width, $height);
    imagedestroy($bg);

    $black = imagecolorallocate($image, 0, 0, 0);
    $font = __DIR__ . '/arial.ttf'; // или любой TTF
    if (is_readable($font)) {
        $bbox = imagettfbbox(16, 0, $font, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $x = ($width - $textWidth) / 2;
        $y = $height / 2 + 6; // компенсация базовой линии
        imagettftext($image, 100, 0, $x, $y, $black, $font, $text);
    } else {
        // fallback на встроенный шрифт
        $x = ($width - strlen($text) * imagefontwidth(5)) / 2;
        $y = ($height - imagefontheight(5)) / 2;
        imagestring($image, 5, $x, $y, $text, $black);
    }

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 5. Кэширование изображений
// ——————————————————————————————————————————————
function getCachedImageOrGenerate(string $cacheDir, string $key, callable $generator): void
{
    $cacheFile = $cacheDir . '/' . md5($key) . '.png';

    // 1. Если кеш есть — отдаём напрямую (быстро и безопасно)
    if (file_exists($cacheFile)) {
        header('Content-Type: image/png');
        header('Cache-Control: max-age=86400'); // кеширование в браузере
        readfile($cacheFile);
        exit;
    }

    // 2. Гарантируем чистый вывод
    if (headers_sent()) {
        http_response_code(500);
        exit('Заголовки уже отправлены');
    }

    // 3. Отключаем вывод ошибок в браузер
    $errorReporting = error_reporting(0);
    ini_set('display_errors', '0');

    // 4. Проверяем директорию
    if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
        http_response_code(500);
        exit('Не удалось создать директорию кеша');
    }

    // 5. Генерируем изображение НАПРЯМУЮ в файл
    $success = false;
    $tempFile = tempnam(sys_get_temp_dir(), 'img_');
    if ($tempFile) {
        $success = $generator($tempFile); // генератор принимает путь для сохранения
        if ($success && file_exists($tempFile)) {
            rename($tempFile, $cacheFile);
            header('Content-Type: image/png');
            readfile($cacheFile);
            exit;
        }
        @unlink($tempFile);
    }

    // 6. Если генерация не удалась — ошибка
    error_reporting($errorReporting);
    http_response_code(500);
    exit('Не удалось сгенерировать изображение');
}

// ——————————————————————————————————————————————
// 6. Простой PDF-документ
// ——————————————————————————————————————————————
function renderSimplePdf(string $message): void
{
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $message, 0, 1, 'C');
    $pdf->Output();
    exit;
}

// ——————————————————————————————————————————————
// 7–9. InvoicePdf class with header, footer, table, logo, and link
// ——————————————————————————————————————————————
class InvoicePdf extends FPDF
{
    function Header()
    {
        // Logo on the left
        $logo = __DIR__ . '/logo.png';
        if (file_exists($logo)) {
            $this->Image($logo, 10, 10, 30);
        }
        // Centered title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Invoice', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function buildTable(array $header, array $data): void
    {
        $this->SetFont('Arial', 'B', 10);
        $w = [80, 40, 40, 30];
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        }
        $this->Ln();
        $this->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            for ($i = 0; $i < count($row); $i++) {
                $this->Cell($w[$i], 6, $row[$i], 1, 0, 'L');
            }
            $this->Ln();
        }
    }

    function renderInvoice(array $items): void
    {
        $this->AddPage();
        $header = ['Item', 'Qty', 'Price', 'Total'];
        $this->buildTable($header, $items);
        $this->Ln(10);
        // Hyperlink
        $this->SetFont('Arial', 'U', 10);
        $this->SetTextColor(0, 0, 255);
        $this->Write(5, 'Visit website');
        $this->Link(
            $this->GetX() - $this->GetStringWidth('Visit website'),
            $this->GetY() - 5,
            $this->GetStringWidth('Visit website'),
            5,
            'https://example.com'
        );
        $this->Output();
        exit;
    }
}

// ——————————————————————————————————————————————
// 10. Final homework: badge.php and PDF invoice
// ——————————————————————————————————————————————
function runHomework(): void
{
    if (isset($_GET['type']) && $_GET['type'] === 'badge') {
        $name = trim($_GET['name'] ?? '');
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]{2,50}$/u', $name)) {
            http_response_code(400);
            exit('Invalid name');
        }

        $cacheDir = __DIR__ . '/cache';
        getCachedImageOrGenerate($cacheDir, 'badge_' . $name, function () use ($name) {
            $bgPath = __DIR__ . '/badge-bg.png';
            if (!is_readable($bgPath)) {
                // fallback
                $image = imagecreatetruecolor(300, 100);
                $white = imagecolorallocate($image, 255, 255, 255);
                $black = imagecolorallocate($image, 0, 0, 0);
                imagefilledrectangle($image, 0, 0, 299, 99, $white);
                imagestring($image, 5, 50, 40, "BADGE: $name", $black);
            } else {
                $bg = imagecreatefrompng($bgPath);
                $width = imagesx($bg);
                $height = imagesy($bg);
                $image = imagecreatetruecolor($width, $height);
                imagecopy($image, $bg, 0, 0, 0, 0, $width, $height);
                imagedestroy($bg);

                $black = imagecolorallocate($image, 0, 0, 0);
                $font = __DIR__ . '/arial.ttf';
                if (is_readable($font)) {
                    imagettftext($image, 18, 0, 30, 60, $black, $font, $name);
                } else {
                    imagestring($image, 5, 30, 50, $name, $black);
                }
            }
            header('Content-Type: image/png');
            imagepng($image);
            imagedestroy($image);
        });
        return;
    }

    if (isset($_GET['type']) && $_GET['type'] === 'invoice') {
        $items = [
            ['Headphones', '2', '1500', '3000'],
            ['Mouse', '1', '800', '800'],
            ['Keyboard', '1', '2500', '2500'],
            ['Monitor', '1', '12000', '12000'],
            ['Mouse Pad', '3', '200', '600']
        ];
        $pdf = new InvoicePdf();
        $pdf->renderInvoice($items);
        return;
    }
    
}
// ——————————————————————————————————————————————
// Потом: вывод HTML-страницы со всеми примерами
// ——————————————————————————————————————————————
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Графика и PDF — Демонстрация</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 20px auto; }
        h2 { margin: 25px 0 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .example { margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 4px; }
        img { max-width: 100%; border: 1px solid #ddd; background: #fff; }
        .error { color: #c33; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Графика и PDF — Демонстрация всех заданий</h1>

    <h2>1. Чёрный квадрат</h2>
    <div class="example">
        <img src="?type=black-square" alt="Чёрный квадрат">
    </div>

    <h2>2. Текст (встроенный шрифт)</h2>
    <div class="example">
        <img src="?type=text&value=Hello+World" alt="Текст">
    </div>

    <h2>3. TrueType-текст</h2>
    <div class="example">
        <img src="?type=ttf&value=Привет+мир!" alt="TTF текст">
        <div class="note">Требуется файл arial.ttf в корне</div>
    </div>

    <h2>4. Кнопка</h2>
    <div class="example">
        <img src="?type=button&value=Купить+сейчас" alt="Кнопка">
        <div class="note">Требуется файл badge-bg.png в корне</div>
    </div>

    <h2>5. Кэширование: значок</h2>
    <div class="example">
        <img src="?type=badge&name=Алексей" alt="Значок">
    </div>

    <h2>6–9. PDF: Счёт с таблицей</h2>
    <div class="example">
        <a href="?type=invoice" target="_blank" class="button">📄 Скачать PDF-счёт</a>
    </div>

    <h2>10. Простой PDF</h2>
    <div class="example">
        <a href="?type=simple-pdf" target="_blank" class="button">📄 Простой PDF</a>
    </div>

    <div class="example">
        <h3>Если изображения не загружаются:</h3>
        <ul>
            <li>Проверьте, установлено ли расширение GD: <?php echo extension_loaded('gd') ? '<span style="color:green">✅ Да</span>' : '<span class="error">❌ Нет</span>'; ?></li>
            <li>Убедитесь, что файлы <code>arial.ttf</code> и <code>badge-bg.png</code> существуют</li>
            <li>Проверьте права на запись в папку <code>cache/</code></li>
        </ul>
    </div>

    <style>
        .button {
            display: inline-block;
            padding: 8px 16px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px 0;
        }
        .note {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</body>
</html>
    <?php
    exit;

// Запуск домашнего задания (роутинг)
runHomework();
?>