Типичный вызов  

```php
<?<?$APPLICATION->IncludeComponent(
        'x:ib.list',
        'rss',
        Array(
                'AJAX_MODE' => 'N',
                'SORT' => ['ID'=>'ASC'],
                'IBLOCK_ID' => \Bxx\Helpers\IBlocks::getIdByCode($Code),
                
                'FILTER' => [
                        'ACTIVE' => 'Y',
                        'ACTIVE_DATE' => 'Y'
                    ],
                'SELECT' => [
                        'NAME',
                        'TIMESTAMP_X',
                        'DETAIL_PAGE_URL',
                        'PREVIEW_TEXT',
                        'DETAIL_TEXT'
                    ],
                
                'CACHE_TYPE' => APPLICATION_ENV=='dev'?'N':'A',
                'CACHE_TIME' => 3600,
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                
                'TEMPLATE' => [
                    'TAGS' => [
                        // 'title' => 'NAME',
                        // 'link' => 'DETAIL_PAGE_URL',
                        // 'description' => 'DETAIL_TEXT',
                        'language' => 'ru',
                    ]
                ]
            )
    );

header('Content-Type: application/rss+xml; charset=utf-8');
```