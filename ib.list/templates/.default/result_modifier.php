<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// изображения
foreach ($arResult['ITEMS'] as $i=>$dctItem) {
    $arResult['ITEMS'][$i]['PREVIEW_PICTURE'] = \CFile::getFileArray($dctItem['PREVIEW_PICTURE']);
    $arResult['ITEMS'][$i]['DETAIL_PICTURE'] = \CFile::getFileArray($dctItem['DETAIL_PICTURE']);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// добавление ключей к кэшируемым
$this->__component->setResultCacheKeys(['BROWSER_TITLE','BROWSER_COLOR']);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// получить базовый фильтр
$arBaseFilter = $this->__component->getFilter();
// например для того чтобы найти сосседей:
$arSelect = Array(
        'IBLOCK_ID',
        'ID',
        'NAME',
        'DETAIL_PAGE_URL'
    );

$db_res = CIBlockElement::GetList(
        $arParams['SORT'],
        $this->__component->getFilter(),
        false,
        ['nElementID' => $arResult['ITEMS'][0]['ID'], 'nPageSize' => 1],
        $arSelect
    );
$rel_rank = 0;
while($arElement = $db_res->GetNext()) {
    if ($arElement['ID'] != $arResult['ITEM']['ID']) {
        $arResult['NEIGHBORS'][$rel_rank] = $arElement;
    } else {
        $rel_rank = 1;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// примененный фильтр можн ополучить из $arResult['FILTER']




///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// SEO
//$ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult['ITEM']['IBLOCK_ID'], $arResult['ITEM']['ID']);
//$arResult['ITEM']['SEO'] = $ipropSectionValues->getValues();
if ($arResult['SECTION']['ID']) {
    $ipropSectionValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arResult['SECTION']['IBLOCK_ID'], $arResult['SECTION']['ID']);
    $arResult['SECTION']['SEO'] = $ipropSectionValues->getValues();
}
