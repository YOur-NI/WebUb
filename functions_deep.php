<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>PHP Functions Demo</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; padding: 20px; line-height: 1.6; }
        h1 { text-align: center; margin-bottom: 30px; color: #007bff; }
        pre { background-color: #fff; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,.1); overflow-x: auto; padding: 15px; white-space: pre-wrap; word-break: break-all; }
        code { display: block; font-size: 14px; }
        br { margin-top: 10px; }
        p { margin-bottom: 15px; }
    </style>
</head>
<body>
<h1>Демонстрация PHP функций</h1>

<p><strong>Проверка числа на простоту:</strong></p>
<pre><code><?php
function isPrime(int $n): bool{
    if ($n < 2)  {
        return false;
    }

    if ($n === 2)  {
        return true;
    }

    for ($i = 3; $i*$i <= $n; $i +=2) {
        if ($n % $i === 0) {
            return false; 
        }
    }
    return true;
}

function answPrime(int $num): string {
    $isPrime = isPrime($num);
    if (!$isPrime) {
        return "False";
    }
    return "True";
}


echo answPrime(1)."<br>\n"; // False
echo answPrime(3)."<br>\n"; // True
echo answPrime(5)."<br>\n"; // True
echo answPrime(6)."<br>\n"; // False
echo answPrime(9)."<br>\n"; // False
echo answPrime(11); // True
?>
</code></pre>

<p><strong>Числа Фибоначчи:</strong></p>
<pre><code><?php
function fibonacci(int $n): int {
    if ($n == 1 || $n == 2) {
        return 1;
    }
    return fibonacci($n - 1) + fibonacci($n - 2);
}


echo fibonacci(6); // Результат: 8
?>
</code></pre>

<p><strong>Форматирование телефонного номера:</strong></p>
<pre><code><?php
function formatPhone(string $phone): string{
    if (strlen($phone) === 11 && is_numeric($phone)) {
        $corrphone = "+7".substr($phone,1,0)."(".substr($phone,1,3).")".substr($phone,4,3)."-".substr($phone,7,2)."-".substr($phone,9);
        return $corrphone;
    }
    return "<br>\n>"."Неверный формат номера";
}


echo formatPhone("89001234567"); // +7(900)123-45-67
echo formatPhone("8efwe323423"); // Неверный формат номера
echo formatPhone("111111111111"); // Неверный формат номера
?>
</code></pre>

<p><strong>Фильтрация массива:</strong></p>
<pre><code><?php
$arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$res = array_filter($arr, function($n){
    return $n % 2 === 0;
});
$i = implode(",", $res );
echo ($i); // Результат: 2,4,6,8,10
?>
</code></pre>

<p><strong>Факторизация с кешированием:</strong></p>
<pre><code><?php
function memoizedFactorial(int $n): int {
    static $cache = [];
    
    if (isset($cache[$n])) {
        return $cache[$n];
    } else {
        if ($n <= 1) {
            $result = 1;
        } else {
            $result = $n * memoizedFactorial($n - 1);
        }
        $cache[$n] = $result;
        return $result;
    }
}


echo memoizedFactorial(5)."<br>\n"; // Результат: 120
echo memoizedFactorial(5)."<br>\n"; // Результат: 120 (получено из кеша)
echo memoizedFactorial(8); // Результат: 40320
?>
</code></pre>

<p><strong>Создание пользователя:</strong></p>
<pre><code><?php
function createUser(string $name, string $email, int $age, bool $isActive = true){
    echo "Пользователь: $name<br>\n Почта: $email <br>\n Возраст: $age лет <br>\n Активность:";
    if ($isActive) {
        echo 'Да';
    } else {
        echo 'Нет';
    }
}

createUser(name: "Игорь", email: "test@test.com", age: 24, isActive: false);
?>
</code></pre>

<p><strong>Генератор счётчика:</strong></p>
<pre><code><?php
function makeCounter(): callable{
    $cnt = 0;
    return function() use (&$cnt) {
        return $cnt++;
    };
}

$counter = makeCounter();

echo $counter()."<br>\n"; // Результат: 0
echo $counter()."<br>\n"; // Результат: 1
echo $counter(); // Результат: 2
?>
</code></pre>

</body>
</html>