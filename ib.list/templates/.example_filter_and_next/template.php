<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<?if(false): // пример вызова?>

<?
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$TAG =  $request->get('TAG');
if ($TAG) {
    $arFiltersByTag = ['PROPERTY_TAGS' => $TAG];
} else {
    $arFiltersByTag = [];
}
?>

<?$APPLICATION->IncludeComponent(
        'x:ib.list',
        'articles',
        Array(
                'ELEMENTS_COUNT' => 1,
                'SORT' => ['SORT'=>'ASC', 'ID'=>'DESC'],
                'FILTER' => [
                        'IBLOCK_ID' => IDIB_ARTICLES,
                        'ACTIVE' => APPLICATION_ENV=='dev'?['Y','N']:'Y',
                    ],
                'FILTERS' => [
                        'TAGS' => $arFiltersByTag,
                    ],
                'SELECT' => [
                        'DETAIL_PAGE_URL',
                        'NAME',
                        'PREVIEW_TEXT',
                        'PROPERTY_NAME__'.LANGUAGE_UID,
                        'PROPERTY_TXT__'.LANGUAGE_UID,
                        'PROPERTY_CYTAMINS',
                        'PREVIEW_PICTURE',
                    ],
                    
                //'KEYS_CACHED' => ['ITEMS','PRELOAD_IMAGES'],
                'PAGER' => [
                        'TITLE' => '',
                        'TEMPLATE' => '',
                        'SHOW_ALWAYS' => 'Y',
                        'SHOW_ALL' => 'N',
                        'PAGE' => 1, // всегда первая страница
                    ],
                'CACHE_TYPE' => APPLICATION_ENV=='dev'?'N':'A',
                'CACHE_TIME' => 3600,
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                
                'TEMPLATE' => [
                    'URI' => $request->getRequestUri() // необхдим для построения ссылок в шаблоне
                ]
            )
    );?>
<?endif?>

<?if(count($arResult['ITEMS'])):?>
<?
$arParamsClined = $component->getParams(); // получаем очищенные от ~ параметры, для сокращения объёма передачи данных
?>

<?if($arParams['TEMPALTE']['ONLY_LIST'] == 'Y'): include('_list.php');
else:?>
<section
        id="container_<?=$arParams['UID']?>"
        data-signed-params="<?=$component->signVal($arParamsClined)?>"
        data-signed-template="<?=$component->signVal($templateName)?>"
    >
    <div class="container">
        <h1 class="bg-black"><?=Loc::getMessage('XCT_ARTICLES_TITLE');?></h1>
        
        <div class="tags">
            <?foreach($arResult['DICTS']['TAGS'] as $dctTag):
            $uri = new \Bitrix\Main\Web\Uri($arParams['TEMPLATE']['URI']);
            $uri->deleteParams(array("PAGEN_".$arResult['NAV_RESULT']->NavNum));
            $uri->addParams(array("TAG" => [$dctTag["UF_XML_ID"]]));

            $arParamsMutation = ['FILTERS' => ['TAGS' => ['PROPERTY_TAGS' => $dctTag["UF_XML_ID"]]], 'TEMPALTE' => $arParams['TEMPLATE']];
            $arParamsMutation['TEMPALTE']['AJAX'] = 'Y';
            
            $signedParamsMutation = $component->signVal($arParamsMutation);
            ?>
            <a
                    class="tag"
                    href="<?=$uri->getUri()?>"
                    onclick="APP.Components.<?=$arParams['UID']?>.go('','<?=$uri->getUri()?>','<?=$signedParamsMutation?>'); return false;"
                ><?=$dctTag['UF_NAME']?></a>
            <?endforeach?>
        </div>
        
        
        <div class="">
            <?include('_list.php');?>
        </div>
    </div>
</section>


<?if ('Y' != $arParams['TEMPLATE']['AJAX']):?>
<script>
    var APP = APP || {};
    APP.Components = APP.Components || {};
    APP.Components.<?=$arParamsClined['UID']?> = APP.Components.<?=$arParamsClined['UID']?> || {
        
        // id контейнера в котором работает скрипт
        idContainer: 'container_<?=$arParamsClined['UID']?>',
        
        container: false,
        
        cache: {},
        
        // переход
        go: function (title,url,signedParamsMutation) {
            var container = document.querySelector('#'+APP.Components.<?=$arParamsClined['UID']?>.idContainer);
            
            if (this.cache[signedParamsMutation]) {
                container.outerHTML = this.cache[signedParamsMutation];
            } else {
                var self = this;
                APP.Components.<?=$arParamsClined['UID']?>.ajaxUpdate(
                        signedParamsMutation,
                        function (response) {
                            self.cache[signedParamsMutation] = response;
                            container.outerHTML = response;
                        }
                    );
            }
            document.title = title;
            history.pushState('', title, url)
        },
        
        // подгрузка следующей страницы
        next: function (signedParamsMutation) {
            APP.Components.<?=$arParamsClined['UID']?>.ajaxUpdate(
                    signedParamsMutation,
                    function (response) {
                        var container = document.querySelector('#'+APP.Components.<?=$arParamsClined['UID']?>.idContainer);
                        container.querySelector('.next').outerHTML = response;
                    }
                );
        },
        
        
        ajaxUpdate: function (signedParamsMutation, callback) {
            var container = document.querySelector('#'+APP.Components.<?=$arParamsClined['UID']?>.idContainer);
            var query = {
                    c: 'x:ib.list',
                    action: 'execute',
                    mode: 'class'
                };
            var request = $.ajax({
                    url: '/bitrix/services/main/ajax.php?' + $.param(query, true),
                    method: 'POST',
                    data: {
                        signedParams: container.dataset.signedParams,
                        signedTemplate: container.dataset.signedTemplate,
                        signedParamsMutation: signedParamsMutation
                    }
                });
            var self = this;
            request.done(callback);
        }
    }
</script>
<?endif?>
<?endif?>
<?endif?>