<?use \Bitrix\Main\Localization\Loc;?>
<?foreach($arResult['ITEMS'] as $dctItem):?>
<div class="">
    <div class=""  style="background-image: url(<?=$dctItem['PREVIEW_PICTURE']['SRC']?>)">
        <h3><?=$dctItem['NAME']?></h3>
        <p class=""><?=$dctItem['PREVIEW_TEXT']?></p>
        <?if($dctItem['DETAIL_PAGE_URL']):?>
        <a class="" href="<?=$dctItem['DETAIL_PAGE_URL']?>" target="_blank"><?=Loc::getMessage('XCT_ARTICLES_MORE');?><span></span></a>
        <?endif?>
    </div>
</div>
<?endforeach?>
<?
if ($arResult['NAV_RESULT']->NavPageNomer < $arResult['NAV_RESULT']->NavPageCount):
    $uri = new \Bitrix\Main\Web\Uri($arParams['TEMPLATE']['URI']);
    $uri->deleteParams(array("PAGEN_".$arResult['NAV_RESULT']->NavNum));
    $arParamsMutation = ['PAGER' => $arParams['PAGER'], 'TEMPALTE' => $arParams['TEMPLATE']];
    $arParamsMutation['PAGER']['PAGE'] = $arResult['NAV_RESULT']->NavPageNomer+1;
    $arParamsMutation['TEMPALTE']['ONLY_LIST'] = 'Y';
    $arParamsMutation['TEMPALTE']['AJAX'] = 'Y';
    
    
    $signedParamsMutation = $component->signVal($arParamsMutation);
    ?>
    <a
            class="next"
            href="<?=$uri->getUri()?>"
            onclick="APP.Components.<?=$arParams['UID']?>.next('<?=$signedParamsMutation?>'); return false;"
        >NEXT</a>
<?endif?>