Типичный вызов

```php
<?$APPLICATION->IncludeComponent(
        'x:ib.list',
        'main',
        Array(
                'AJAX_MODE' => 'N',
                'ELEMENTS_COUNT' => 1,
                'SORT' => ['SORT'=>'ASC'],
                //'IBLOCK_ID' => \Bxx\Helpers\IBlocks::getIdByCode('slider'),
                
                'FILTER' => [
                        'IBLOCK_ID' => \Bxx\Helpers\IBlocks::getIdByCode('slider'),
                        'ACTIVE' => 'Y',
                        'ACTIVE_DATE' => 'Y'
                    ],
                'FILTERS' => [
                    'TAGS' => ['PROPERTY_TAGS' => 'слово'],
                ],
                'SELECT' => [
                        'NAME',
                        'TIMESTAMP_X',
                        'DATE_ACTIVE_FROM',
                        'DETAIL_PAGE_URL',
                        'PREVIEW_PICTURE',
                        'PROPERTY_YOUTUBE',
                        'PROPERTY_AUDIO'
                    ],
                    
                'KEYS_CACHED' => ['ITEMS','PRELOAD_IMAGES'],
                
                'CACHE_TYPE' => APPLICATION_ENV=='dev'?'N':'A',
                'CACHE_TIME' => 3600,
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                
                
                'PAGER' => [
                        'TITLE' => '',
                        'TEMPLATE' => '',
                        'SHOW_ALWAYS' => 'N',
                        'SHOW_ALL' => 'N',
                        'PAGE' => 1,
                    ],
                
                
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


Ajax вызов:

execute принимает три праметра:
signedParams: подписанный набор параметров
signedTemplate: подписанный шаблон в котором необходимо отрендерить компонента
signedParamsMutation: подписанная мутация для параметров - diff между дефолтными параметрами и например дополнительным фильтром
Все значения из Params будут заменены на значения из ParamsMutation (не рекурсивно)

Пример использования фильтрации + догрузски см. в шаблоне .example_filter_and_next