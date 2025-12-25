<?php

declare(strict_types=1);

// #1. Простейшее наследование
class Product {
    public function __construct(
        protected string $title,
        protected float $price
    ) {}

    public function getTitle(): string {
        return $this->title;
    }
}

class Book extends Product {
    public function __construct(
        string $title,
        float $price,
        private string $author
    ) {
        parent::__construct($title, $price);
    }

    public function getAuthor(): string {
        return $this->author;
    }
}

// #2. Абстрактные классы
abstract class Lesson {
    abstract public function cost(): int;
    abstract public function chargeType(): string;
}

class Lecture extends Lesson {
    public function cost(): int { return 30; }
    public function chargeType(): string { return "Фиксированная ставка"; }
}

class Seminar extends Lesson {
    public function cost(): int { return 50; }
    public function chargeType(): string { return "Почасовая ставка"; }
}

// #3. Интерфейсы
interface Bookable {
    public function book(): void;
}

interface Chargeable {
    public function calculateFee(): float;
}

class Workshop implements Bookable, Chargeable {
    public function book(): void {
        echo "Мероприятие забронировано.<br>\n";
    }

    public function calculateFee(): float {
        return 5000.0;
    }
}

// #4. Программирование на основе интерфейса
function processBooking(Bookable $item): void {
    $item->book();
}

// #5. Трейты: базовое использование
trait PriceUtilities {
    public function calculateTax(float $price): float {
        return $price * 0.2;
    }
}

// #6. Несколько трейтов
trait IdentityTrait {
    public function generateId(): string {
        return uniqid();
    }
}

// Обновляем ShopProduct, чтобы он использовал оба трейта
class ShopProduct {
    use PriceUtilities, IdentityTrait;

    public function __construct(private string $title, private float $price) {}

    public function getPriceWithTax(): float {
        return $this->price + $this->calculateTax($this->price);
    }
}

// #7. Разрешение конфликтов трейтов
trait Printer {
    public function output(): void {
        echo "A<br>\n";
    }
}

trait LoggerOutput {
    public function output(): void {
        echo "B<br>\n";
    }
}

class Report {
    use Printer, LoggerOutput {
        LoggerOutput::output insteadof Printer;
        Printer::output as bar;
    }
}

// #8. Трейт с доступом к свойствам хост-класса
trait Describer {
    public function describe(): string {
        return "Объект: {$this->name}";
    }
}

class Item {
    use Describer;
    public function __construct(public string $name) {}
}

// #9. Абстрактные методы в трейтах
trait Validation {
    abstract public function getRules(): array;

    public function validate(): bool {
        return true;
    }
}

class UserForm {
    use Validation;

    public function getRules(): array {
        return ['email' => 'required'];
    }
}

// #10. Комплексное задание: расширение ShopProduct
interface HasMedia {
    public function getMediaLength(): int;
}

trait TaxCalculation {
    public function getTax(): float {
        return $this->price * 0.2;
    }
}

class BookProduct implements HasMedia {
    use TaxCalculation;
    public function __construct(private string $title, private float $price) {}
    public function getMediaLength(): int { return 300; } // страниц
}

class CDProduct implements HasMedia {
    use TaxCalculation;
    public function __construct(private string $title, private float $price) {}
    public function getMediaLength(): int { return 74; } // минут
}

// #11. Итоговое домашнее задание

// Абстрактный класс Service
abstract class Service {
    abstract public function getDuration(): int;
    abstract public function getDescription(): string;
}

// Интерфейс Schedulable
interface Schedulable {
    public function schedule(): string;
}

// Универсальный трейт Logger (без привязки к свойству title)
trait Logger {
    public function log(string $msg): void {
        echo "[LOG] $msg<br>\n";
    }
}

// Класс Consulting — соответствует заданию: наследуется от Service, реализует Schedulable, использует Logger
class Consulting extends Service implements Schedulable {
    use Logger;

    public function __construct(private string $description, private int $duration) {}

    public function getDuration(): int {
        return $this->duration;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function schedule(): string {
        $this->log("Консультация запланирована");
        return "Консультация запланирована";
    }
}

// Класс Training — аналогично
class Training extends Service implements Schedulable {
    use Logger;

    public function __construct(private string $description, private int $duration) {}

    public function getDuration(): int {
        return $this->duration;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function schedule(): string {
        $this->log("Тренинг запланирован");
        return "Тренинг запланирован";
    }
}

//////////////////////////////////////////////

echo "1. Простейшее наследование<br>\n";
$book = new Book("1984", 12.99, "George Orwell");
echo $book->getTitle() . "<br>\n";
echo $book->getAuthor() . "<br>\n";

echo "<br>\n2. Абстрактные классы<br>\n";
$lecture = new Lecture();
echo $lecture->cost() . "<br>\n";
echo $lecture->chargeType() . "<br>\n";

$seminar = new Seminar();
echo $seminar->cost() . "<br>\n";
echo $seminar->chargeType() . "<br>\n";

echo "<br>\n3. Интерфейсы<br>\n";
$workshop = new Workshop();
echo $workshop->calculateFee() . "<br>\n";

echo "<br>\n4. Программирование на основе интерфейса<br>\n";
processBooking(new Workshop());

$another = new class implements Bookable {
    public function book(): void {
        echo "Анонимное бронирование.<br>\n";
    }
};
processBooking($another);

echo "<br>\n5. Трейты: базовое использование<br>\n";
$shopProduct = new ShopProduct("Книга о PHP", 10.0);
echo $shopProduct->getPriceWithTax() . "<br>\n";

echo "<br>\n6. Несколько трейтов<br>\n";
echo $shopProduct->generateId() . "<br>\n";
echo $shopProduct->calculateTax(100.0) . "<br>\n";

echo "<br>\n7. Разрешение конфликтов трейтов<br>\n";
$report = new Report();
$report->output(); // B
$report->bar();    // A

echo "<br>\n8. Трейт с доступом к свойствам хост-класса<br>\n";
$item = new Item("Тест");
echo $item->describe() . "<br>\n";

echo "<br>\n9. Абстрактные методы в трейтах<br>\n";
$userForm = new UserForm();
echo ($userForm->validate() ? 'true' : 'false') . "<br>\n";

echo "<br>\n10. Комплексное задание: расширение ShopProduct<br>\n";
$bookProd = new BookProduct("PHP Guide", 1500.0);
$cdProd = new CDProduct("Jazz Mix", 800.0);
echo $bookProd->getMediaLength() . "<br>\n";
echo $bookProd->getTax() . "<br>\n";
echo $cdProd->getMediaLength() . "<br>\n";
echo $cdProd->getTax() . "<br>\n";

echo "<br>\n11. Итоговое домашнее задание<br>\n";
$consulting = new Consulting("Экспертная консультация", 60);
$training = new Training("Обучение PHP", 180);

echo $consulting->getDescription() . " (" . $consulting->getDuration() . " мин)<br>\n";
echo $training->getDescription() . " (" . $training->getDuration() . " мин)<br>\n";
echo $consulting->schedule() . "<br>\n";
echo $training->schedule() . "<br>\n";
?>