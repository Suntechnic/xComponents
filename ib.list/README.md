Типичный вызов


<?$APPLICATION->IncludeComponent(
            'x:ib.list',
            'slider',
            Array(
                    'AJAX_MODE' => 'N',
                    'IBLOCK_ID' => IDIB_SLIDER,
                    'ELEMENTS_COUNT' => 20,
                    'SORT' => ['SORT'=>'ASC'],
                    'FILTER' => [],
                    'SELECT' => [
                            'PROPERTY_TITLE',
                            'PROPERTY_TEXT',
                            'PROPERTY_URL',
                            'PROPERTY_TMENU',
                            'PREVIEW_PICTURE',
                            'DETAIL_PICTURE'
                        ],
                        
                    'KEYS_CACHED' => ['ITEMS','PRELOAD_IMAGES'],
                    
                    'CACHE_TYPE' => 'Y',
                    'CACHE_TIME' => 3600,
                    'PAGER' => 'N',
                    
                    'PAGER_TITLE' => '',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_TEMPLATE' => '',
                    'PAGER_SHOW_ALL' => 'Y',
                    
                    'AJAX_OPTION_SHADOW' => 'Y',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'Y',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    
                    'TEMPLATE' => [
                        
                    ]
                )
        );?>



