Типичный вызов


<?$APPLICATION->IncludeComponent(
            'x:ib.apd',
            '',
            Array(
                    'UID' => индентификатор,
                    'IBLOCK_ID' => IDIB_FEEDBACK,
                    'ID' => 0m
                    
                    'FIELDS' => [], // коды свойств которые можно/добавлять обновлять
                    'PROPERTIES' => [], // коды свойств которые можно/добавлять обновлять
                    
                    //'FIELD_VALUES' => [], // значения полей элемента (если этот массив есть - выполняется апдейт)
                    //'PROPERTY_VALUES' => [], // значения свойств элемнта
                    
                    'KEYS_CACHED' => ['ITEMS','PRELOAD_IMAGES'],
                    
                    'TEMPLATE' => [
                        'SHOW_FILTER' => 'Y'
                    ]
                )
        );?>



