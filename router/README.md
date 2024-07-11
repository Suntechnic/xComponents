# Роутер

Пример правила для urlrewrite
```php
array (
        'CONDITION' => '#^/api/(.*)#',
        'RULE' => '',
        'ID' => 'x:router',
        'PATH' => '/api/index.php',
        'SORT' => 1,
    )
```

Пример вызова
```php
$APPLICATION->IncludeComponent(
        'x:router',
        '',
        Array(
                'CONFIG' => ['api.php']
            )
    );
```

В ключе CONFIG нужно передать массив имен файлов так как бы вы его прописали в /bitrix/.settings.php