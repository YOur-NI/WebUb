<?php

#1. Создание класса и объекта
class Person{
    public string $name;
    public int $age;
}

#2. Свойства с разной видимостью
class Product{
    public string $title;
    protected int $stock = 0;
    private float $price = 0.0;

    public function setPrice(float $price): void{
        $this->price = $price;
    }
    public function getPrice(): float{
        return $this->price;
    }
}

#3. Методы и ключевое слово $this
class Greeter {
        private string $greeting;

    public function __construct(string $greeting){
        $this->greeting = $greeting;
    }

    public function greet(string $name): string{
        return $this->greeting.", ".$name."!";
    }
}

#4. Конструктор с promoted properties (PHP 8)
class Book{
    public function __construct(
        private string $title,
        private string $author,
        private int $year
    ) {}

    public function getInfo(): string
    {
        return "«{$this->title}» ({$this->author}, {$this->year})";
    }
}

#5. Инкапсуляция: защита данных
class BankAccount {
    private float $balance = 0.0;

    public function deposit(float $amount):void{
        if ($amount > 0){$this->balance+=$amount;}
    }

    public function withdraw(float $amount): bool{
        if ($amount > 0 && $this->balance >= $amount) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }

    public function getBalance(): float{
        return $this->balance;
    }
}

#6. Практический пример: каталог товаров
class ShopProduct{

    public function __construct(
        private string $title,
        private string $producer,
        private float $price
    ) {}

    public function getSummaryLine(): string {
        return "{$this->title} ({$this->producer}) — {$this->price} ₽";
    }
}

#7. Статические свойства и методы
class Counter {
    private static int $count = 0;

    public function __construct() {
        self::$count++;
    }

    public static function getCount(): int {
        return self::$count;
    }
}

#8. Итоговое задание: класс User
class User{
    public function __construct(
        private string $email,
        private string $name,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getInfo(): string
    {
        return "{$this->name} ({$this->email}), зарегистрирован: {$this->createdAt->format('Y-m-d')}";
    }
}

//////////////////////////////////


echo "1. Создание класса и объекта.<br>\n";
$nick = new Person();
$nick->name = 'Некит';
$nick->age = 19;

$kcin = new Person();
$kcin->name = 'Кит';
$kcin->age = 91;

echo "Person 1: Имя = " . $nick->name . ", Возраст = " . $nick->age . "<br>\n";
echo "Person 2: Имя = " . $kcin->name . ", Возраст = " . $kcin->age . "\n";


echo "<br>\n";
echo "<br>\n2. Свойства с разной видимостью.<br>\n";
$product = new Product("Орешки");
$product->setPrice(999.99);
echo "Цена: " . $product->getPrice();


echo "<br>\n";
echo "<br>\n3. Методы и ключевое слово this.<br>\n";
$greeter = new Greeter("Привет");
echo $greeter->greet("Алексей") . "<br>\n";

$greeter2 = new Greeter("Здравствуйте");
echo $greeter2->greet("Мария") . "\n";


echo "<br>\n";
echo "<br>\n4. Конструктор с promoted properties (PHP 8).<br>\n";
$book = new Book("1984", "Джордж Оруэлл", 1949);
echo $book->getInfo();


echo "<br>\n";
echo "<br>\n5. Инкапсуляция: защита данных.<br>\n";
$acc = new BankAccount();
$acc->deposit(100);
$acc->withdraw(30);
echo $acc->getBalance();


echo "<br>\n";
echo "<br>\n6. Практический пример: каталог товаров.<br>\n";
$product = new ShopProduct("Чай", "Имбирный", 399.0);
echo $product->getSummaryLine();


echo "<br>\n";
echo "<br>\n7. Статические свойства и методы.<br>\n";
$cnt1 = new Counter();
$cnt2 = new Counter();
$cnt3 = new Counter();
echo $cnt3->getCount();

echo "<br>\n";
echo "<br>\n8. Итоговое задание: класс User.<br>\n";
$user1 = new User("ivan@example.com", "Иван");
$user2 = new User("maria@test.org", "Мария", new \DateTimeImmutable("2024-01-15"));

// Вывод информации
echo $user1->getInfo() . "<br>\n";
echo $user2->getInfo() . "\n";

?>