<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach ($arResult['ITEMS'] as $i=>$dctItem) {
    $arResult['ITEMS'][$i]['PREVIEW_PICTURE'] = \CFile::getFileArray($dctItem['PREVIEW_PICTURE']);
    $arResult['ITEMS'][$i]['DETAIL_PICTURE'] = \CFile::getFileArray($dctItem['DETAIL_PICTURE']);
}