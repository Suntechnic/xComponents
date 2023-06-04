Типичный вызов

```php
<?$APPLICATION->IncludeComponent(
        'x:hlb.list',
        '',
        Array(
                'AJAX_MODE' => 'N',
                'SORT' => ['ID'=>'ASC'],
                'HLBLOCK_ID' => 2,
                
                'FILTER' => [
                        'UF_XML_ID' => 'code',
                    ],
                'SELECT' => [
                        'UF_NAME',
                        'UF_XML_ID'
                    ],
                    
                'KEYS_CACHED' => ['ITEMS'],
                
                'CACHE_TYPE' => APPLICATION_ENV=='dev'?'N':'A',
                'CACHE_TIME' => 3600,
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                
                'AJAX_OPTION_SHADOW' => 'Y',
                'AJAX_OPTION_JUMP' => 'N',
                'AJAX_OPTION_STYLE' => 'Y',
                'AJAX_OPTION_HISTORY' => 'N',
                'AJAX_OPTION_ADDITIONAL' => '',
                
                'TEMPLATE' => [
                    'SHOW_FILTER' => 'Y'
                ]
            )
    );?>
```



