<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// дата последней модификации
//\Bitrix\Main\Context::getCurrent()->getResponse()->setLastModified($arResult["ITEMS_TIMESTAMP_X"]);

// установка заголовка
//$dctSeo = $arResult['ITEM']['SEO'];
//
//if ($dctSeo['ELEMENT_META_TITLE']) {
//    $APPLICATION->SetPageProperty('title', $dctSeo['ELEMENT_META_TITLE'], ['COMPONENT_NAME' => $component->getName()]);
//} else {
//    $APPLICATION->SetPageProperty('title', $arResult['ITEM']['NAME'], ['COMPONENT_NAME' => $component->getName()]);
//}
//
//if ($dctSeo['ELEMENT_META_DESCRIPTION']) {
//    $APPLICATION->SetPageProperty('description', $dctSeo['ELEMENT_META_DESCRIPTION'], ['COMPONENT_NAME' => $component->getName()]);
//} else {
//    $APPLICATION->SetPageProperty('description', $arResult['ITEM']['NAME'], ['COMPONENT_NAME' => $component->getName()]);
//}


$dctSeo = $arResult['SECTION']['SEO'];

if ($dctSeo['SECTION_META_TITLE']) {
    $APPLICATION->SetPageProperty('title', $dctSeo['SECTION_META_TITLE'], ['COMPONENT_NAME' => $component->getName()]);
} else {
    $APPLICATION->SetPageProperty('title', $arResult['SECTION']['NAME'], ['COMPONENT_NAME' => $component->getName()]);
}

if ($dctSeo['SECTION_META_DESCRIPTION']) {
    $APPLICATION->SetPageProperty('description', $dctSeo['SECTION_META_DESCRIPTION'], ['COMPONENT_NAME' => $component->getName()]);
} else {
    $APPLICATION->SetPageProperty('description', $arResult['SECTION']['NAME'], ['COMPONENT_NAME' => $component->getName()]);
}
