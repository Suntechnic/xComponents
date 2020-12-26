Типичный вызов


<?$APPLICATION->IncludeComponent(
            'x:ib.list',
            'main',
            Array(
                    'AJAX_MODE' => 'N',
                    'ELEMENTS_COUNT' => 1,
                    'SORT' => ['SORT'=>'ASC'],
                    'IBLOCK_ID' => IDIB_PROJECTS,
                    
                    'FILTER' => [
                            'IBLOCK_ID' => IDIB_PROJECTS,
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
                    
                    'CACHE_TYPE' => 'Y',
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



