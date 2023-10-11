Типичный вызов

```
<?$APPLICATION->IncludeComponent(
            'x:ib.sections',
            '',
            Array(
                    'IBLOCK_ID' => IDIB_CATALOG,
                    
                    'FILTER' => [
                            'IBLOCK_ID' => IDIB_CATALOG,
                        ],
                    'FILTERS' => [],
                    'SELECT' => [
                            'NAME',
                            'CODE',
                            'PICTURE',
                            'SECTION_PAGE_URL'
                        ],
                        
                    'KEYS_CACHED' => ['ITEMS','PRELOAD_IMAGES'],
                    
                    'CACHE_TYPE' => APPLICATION_ENV=='dev'?'N':'A',
                    'CACHE_TIME' => 3600,
                    'CACHE_FILTER' => 'Y',
                    'CACHE_GROUPS' => 'Y',
                )
        );?>
```
