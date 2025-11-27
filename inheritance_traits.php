<?php
#inheritance_traits.php
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

// #3. Интерфейсы
////////////////////////////////////////////////////////
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

class Consultancy implements Bookable, Chargeable {
    public function book(): void {
        echo "Консультация забронирована.<br>\n";
    }

    public function calculateFee(): float {
        return 1500.0;
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

class ShopProduct {
    use PriceUtilities;

    public function __construct(private string $title, private float $price) {}

    public function getPriceWithTax(): float {
        return $this->price + $this->calculateTax($this->price);
    }
}

// #6. Несколько трейтов
trait IdentityTrait {
    public function generateId(): string {
        return uniqid();
    }
}

class MultiTraitShopProduct {
    use PriceUtilities, IdentityTrait;
}

// #7. Разрешение конфликтов трейтов
trait A { public function foo() { echo "A<br>\n"; } }
trait B { public function foo() { echo "B<br>\n"; } }

class C {
    use A, B {
        B::foo insteadof A;
        A::foo as bar;
    }
}

// #8. Трейт с доступом к свойствам хост-класса
trait Logger {
    public function log(string $msg): void {
        echo "[LOG] $msg (объект: {$this->title})<br>\n";
    }
}

class LoggableProduct {
    use Logger;
    public function __construct(public string $title) {}
}

// #9. Абстрактные методы в трейтах
trait Validation {
    abstract public function getRules(): array;

    public function validate(): bool {
        $rules = $this->getRules();
        // логика валидации...
        return true;
    }
}

class User {
    use Validation;

    public function getRules(): array {
        return ['name' => 'required', 'email' => 'email'];
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

#11. Итоговое домашнее задание

// 1. Абстрактный класс Service
abstract class Service {
    abstract public function getDuration(): int;
    abstract public function getDescription(): string;
}

// 2. Дочерние классы Consulting и Training
class Consulting extends Service {
    public function __construct(private string $description, private int $duration) {}

    public function getDuration(): int {
        return $this->duration;
    }

    public function getDescription(): string {
        return $this->description;
    }
}

class Training extends Service {
    public function __construct(private string $description, private int $duration) {}

    public function getDuration(): int {
        return $this->duration;
    }

    public function getDescription(): string {
        return $this->description;
    }
}

// 3. Интерфейс Schedulable
interface Schedulable {
    public function schedule(): string;
}

// 4. Реализация интерфейса в классах
class ConsultingService extends Service implements Schedulable {
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
        return "Консультация запланирована на " . $this->getDuration() . " часов";
    }
}

class TrainingService extends Service implements Schedulable {
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
        return "Тренинг запланирован на " . $this->getDuration() . " часов";
    }
}

// 5. Трейт Logger
trait Logger {
    public function log(string $msg): void {
        echo "[LOG] $msg\n";
    }
}



//////////////////////////////////////////////

echo "1. Простейшее наследование<br>\n";
$book = new Book("1984", 12.99, "George Orwell");
echo $book->getTitle() . "<br>\n";  // Выведет: 1984
echo $book->getAuthor() . "<br>\n"; // Выведет: George Orwell


echo "<br>\n2. Абстрактные классы<br>\n";
$lecture = new Lecture();
echo $lecture->cost() . "<br>\n";       // Выведет: 30
echo $lecture->chargeType() . "<br>\n"; // Выведет: Фиксированная ставка


echo "<br>\n3. Интерфейсы<br>\n";
$consultancy = new Consultancy();
echo $consultancy->calculateFee() . "<br>\n"; // Выведет: 1500


echo "<br>\n4. Программирование на основе интерфейса<br>\n";
processBooking(new Workshop());     // Выведет: Мероприятие забронировано.
processBooking(new Consultancy()); // Выведет: Консультация забронирована.


echo "<br>\n5. Трейты: базовое использование<br>\n";
$shopProduct = new ShopProduct("Книга о PHP", 10.0);
echo $shopProduct->getPriceWithTax() . "<br>\n"; // Выведет: 12


echo "<br>\n6. Несколько трейтов<br>\n";
$multiTraitProduct = new MultiTraitShopProduct();
echo $multiTraitProduct->generateId() . "<br>\n"; // Выведет уникальный ID
echo $multiTraitProduct->calculateTax(100.0) . "<br>\n"; // Выведет: 20


echo "<br>\n7. Разрешение конфликтов трейтов<br>\n";
$c = new C();
$c->foo(); // Выведет: B
$c->bar(); // Выведет: A


echo "<br>\n8. Трейт с доступом к свойствам хост-класса<br>\n";
$loggableProduct = new LoggableProduct("Ноутбук");
$loggableProduct->log("Создан новый продукт"); // Выведет: [LOG] Создан новый продукт (объект: Ноутбук)


echo "<br>\n9. Абстрактные методы в трейтах<br>\n";
$user = new User();
echo $user->validate() ? "true<br>\n" : "false<br>\n"; // Выведет: true


echo "<br>\n10. Комплексное задание: расширение ShopProduct<br>\n";
$bookProduct = new BookProduct("Справочник PHP", 20.0);
$cdProduct = new CDProduct("Альбом U2", 15.0);

echo $bookProduct->getMediaLength() . "<br>\n"; // Выведет: 300
echo $bookProduct->getTax() . "<br>\n";         // Выведет: 4
echo $cdProduct->getMediaLength() . "<br>\n";   // Выведет: 74
echo $cdProduct->getTax() . "<br>\n";           // Выведет: 3


echo "<br>\n11. Тестирование классов Consulting и Training с трейтом Logger:<br>\n";
$consulting = new ConsultingService("Базовая консультация", 2);
$training = new TrainingService("Введение в PHP", 8);

echo $consulting->schedule() . "<br>\n";
echo $training->schedule() . "<br>\n";
?>