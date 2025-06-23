<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка обратной связи</title>
</head>
<body>
<h2>Новая заявка обратной связи</h2>
<p><strong>Имя:</strong> {{ $callback->name }}</p>
<p><strong>Телефон:</strong> {{ $callback->phone }}</p>
<p><strong>Текст:</strong> {{ $callback->text }}</p>
<p><strong>Согласие на обработку данных:</strong> {{ $callback->agree ? 'Да' : 'Нет' }}</p>
<p><strong>Статус:</strong> {{ $callback->status }}</p>
</body>
</html>
