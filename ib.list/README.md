Типичный вызов

```
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

```
$arParamsClined = $component->getParams(); // получаем очищенные от ~ параметры, для сокращения объёма передачи данных
<script>
    var APP = APP || {};
    APP.Components = APP.Components || {};
    
    // используем UID для изоляции параметров
    APP.Components.<?=$arParamsClined['UID']?> = APP.Components.<?=$arParamsClined['UID']?> || {
        signedParams:  '<?=$component->signVal($arParamsClined)?>',
        signedTemplate: '<?=$component->signVal($templateName)?>',
        
        idContainer: 'container_<?=$arParamsClined['UID']?>',
        
        cache: {},
        
        go: function (title,url,signedParamsMutation) {
            if (this.cache[signedParamsMutation]) {
                this.update(this.cache[signedParamsMutation])
            } else {
                APP.Components.<?=$arParamsClined['UID']?>.ajaxUpdate(
                        signedParamsMutation
                    );
            }
            document.title = title;
            history.pushState('', title, url)
        },
        
        update: function (content) {
            $('#'+APP.Components.<?=$arParamsClined['UID']?>.idContainer).replaceWith(content);
        },
        
        ajaxUpdate: function (signedParamsMutation, elm) {
            var query = {
                    c: 'x:ib.list',
                    action: 'execute',
                    mode: 'class'
                };
            var request = $.ajax({
                    url: '/bitrix/services/main/ajax.php?' + $.param(query, true),
                    method: 'POST',
                    data: {
                        signedParams: APP.Components.<?=$arParamsClined['UID']?>.signedParams,
                        signedTemplate: APP.Components.<?=$arParamsClined['UID']?>.signedTemplate,
                        signedParamsMutation: signedParamsMutation
                    }
                });
            var self = this;
            request.done(function (response) {
                    self.cache[signedParamsMutation] = response
                    self.update(response)
                    //AOS.refreshHard();
                });
        }
    }
</script>

```