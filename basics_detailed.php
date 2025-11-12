<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Basics Examples</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            max-width: 800px;
        }
        .section {
            margin-bottom: 30px;
            padding: 15px;
            border-left: 4px solid #007bff;
            background-color: #f8f9fa;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
        .code-output {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>Примеры PHP кода</h1>

    <div class="section">
        <h2>Функция классификации возраста</h2>
        <?php
        function classifyAge(int $age): string
        {
            if ($age < 12) {
                return "Ребёнок";
            } elseif ($age > 12 && $age <= 17) {
                return "Подросток";
            } else {
                return "Подросток";
            }
        }

        echo classifyAge(8) . "<br>\n";
        echo classifyAge(15) . "<br>\n";
        echo classifyAge(25) . "<br>\n";
        ?>
    </div>

    <div class="section">
        <h2>Список городов</h2>
        <ul>
        <?php
        $cities = ['Абвгдейск', 'Екб', 'Пышма', 'Тавда', 'Ревда'];

        foreach ($cities as $city){
            $city2 = htmlspecialchars($city);
            echo "    <li>{$city2}</li>\n";
        }
        ?>
        </ul>
    </div>

    <div class="section">
        <h2>Алгоритм FizzBuzz</h2>
        <div class="code-output">
        <?php
        for ($i = 1; $i <= 100; $i++) {
            if ($i % 3 === 0 && $i % 5 === 0) {
                echo "FizzBuzz\n";
            } elseif ($i % 3 === 0) {
                echo "Fizz\n";
            } elseif ($i % 5 === 0) {
                echo "Buzz\n";
            } else {
                echo $i . "\n";
            }
        }
        ?>
        </div>
    </div>

    <div class="section">
        <h2>Конвертер температур</h2>
        <?php
        function convertCelsiusToFahrenheit(float $celsius): float
        {
            return $celsius * 9/5 + 32;
        }
        echo convertCelsiusToFahrenheit(0) . "<br>\n";
        echo convertCelsiusToFahrenheit(25) . "<br>\n";
        echo convertCelsiusToFahrenheit(-10) . "<br>\n";
        echo convertCelsiusToFahrenheit(100) . "<br>\n";
        ?>
    </div>

    <div class="section">
        <h2>Работа с union types</h2>
        <?php
        function getUserName(int|string $id): string|false{
            if ($id === 1) {
                return "Администратор";
            } elseif ($id === "guest") {
                return "Гость";
            } else {
                return false;
            }
        }

        $result1 = getUserName(1);
        if ($result1 === false) {
            echo "Пользователь не найден<br>\n";
        } else {
            echo $result1 . "<br>\n";
        }

        $result2 = getUserName("guest");
        if ($result2 === false) {
            echo "Пользователь не найден<br>\n";
        } else {
            echo $result2 . "<br>\n";
        }

        $result3 = getUserName(5);
        if ($result3 === false) {
            echo "Пользователь не найден<br>\n";
        } else {
            echo $result3 . "<br>\n";
        }
        ?>
    </div>

    <div class="section">
        <h2>Использование конструкции match</h2>
        <?php
        function classifyAgeWithMatch(int $age): string
        {
            return match (true) {
                $age < 12 => "Ребёнок",
                $age >= 12 && $age <= 17 => "Подросток",
                default => "Взрослый"
            };
        }

        echo classifyAgeWithMatch(8) . "<br>\n";
        echo classifyAgeWithMatch(15) . "<br>\n";
        echo classifyAgeWithMatch(25) . "<br>\n";
        ?>
    </div>
</body>
</html>