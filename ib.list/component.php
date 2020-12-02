<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CIntranetToolbar $INTRANET_TOOLBAR */


/*
<?$APPLICATION->IncludeComponent(
            'x:ib.list',
            '',
            Array(
                    'AJAX_MODE' => 'N',
                    'IBLOCK_ID' => IDIB_SLIDER,
                    'ELEMENTS_COUNT' => 200,
                    'SORT' => ['ID'=>'ASC'],
                    'FILTER' => [],
                    'SELECT' => [
                            'PROPERTY_NAME__'.strtoupper(LANGUAGE_ID),
                            'PROPERTY_TEXT__'.strtoupper(LANGUAGE_ID),
                            'PROPERTY_URL',
                            'PREVIEW_PICTURE',
                            'DETAIL_PICTURE'
                        ],
                    'GETFILE_KEYS' => ['PREVIEW_PICTURE','DETAIL_PICTURE'],
                    
                    'CACHE_TYPE' => 'N',
                    'CACHE_TIME' => 3600,
                    'CACHE_FILTER' => 'Y',
                    'CACHE_GROUPS' => 'Y',
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
                    
                    'TEMPLATE' => []
                )
        );?>
*/

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!isset($arParams["CACHE_TIME"])) $arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if($arParams["IBLOCK_TYPE"] == '') $arParams["IBLOCK_TYPE"] = "news";

$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);

if (!is_array($arParams["SELECT"])) {
	$arParams["SELECT"] = ['ID','IBLOCK_ID'];
} else {
	if (!in_array('ID',$arParams["SELECT"])) $arParams["SELECT"][] = 'ID';
	if (!in_array('IBLOCK_ID',$arParams["SELECT"])) $arParams["SELECT"][] = 'IBLOCK_ID';
}

if (!is_array($arParams["FILTER"])) $arParams["FILTER"] = [];

if (!is_array($arParams["SORT"])) $arParams["SORT"] = ['ID'=>'ASC'];

$arParams["ELEMENTS_COUNT"] = intval($arParams["ELEMENTS_COUNT"]);
if($arParams["ELEMENTS_COUNT"]<=0) $arParams["ELEMENTS_COUNT"] = 20;

$arParams["CACHE_FILTER"] = $arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0) $arParams["CACHE_TIME"] = 0;

$arParams["CHECK_PERMISSIONS"] = $arParams["CHECK_PERMISSIONS"]!="N";

if($arParams["PAGER"] == 'Y') {
	$arNavParams = array(
		"nPageSize" => $arParams["ELEMENTS_COUNT"],
		"bDescPageNumbering" => 'N',
		"bShowAll" => $arParams["PAGER_SHOW_ALL"],
	);
	$arNavigation = CDBResult::GetNavParams($arNavParams);
} else {
	$arNavParams = array(
		"nTopCount" => $arParams["ELEMENTS_COUNT"],
		"bDescPageNumbering" => 'N',
	);
	$arNavigation = false;
}

$pagerParameters = array();

if($this->startResultCache(
		false,
		array(
				$arNavigation,
				$arrFilter,
				$pagerParameters
			)
	)) {
	
	if(!Loader::includeModule('iblock')) {
		$this->abortResultCache();
		ShowError('x:ib.list - not module');
		return;
	}
	

	$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"ID" => $arParams["IBLOCK_ID"],
		));


	$arResult = $rsIBlock->GetNext();
	if (!$arResult) {
		$this->abortResultCache();
		return;
	}
	
	//SELECT
	$arSelect = $arParams["SELECT"];

	//WHERE
	$arFilter = $arParams["FILTER"];
	$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];

	//ORDER BY
	$arSort = $arParams["SORT"];

	$arResult["ITEMS"] = array();
	$arResult["ELEMENTS"] = array();
	$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);
	while ($arItem = $rsElement->GetNext()) {
		
		//$ipropValues = new Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
		//$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();
		
		if (count($arParams['GETFILE_KEYS'])) {
			\Bitrix\Iblock\Component\Tools::getFieldImageData(
					$arItem,
					$arParams['GETFILE_KEYS'],
					Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT
				);
		}
		
		
		
		$arResult["ITEMS"][] = $arItem;
	}
	unset($arItem);

	$navComponentParameters = array();

	$arResult["NAV_STRING"] = $rsElement->GetPageNavStringEx(
			$navComponentObject,
			$arParams["PAGER_TITLE"],
			$arParams["PAGER_TEMPLATE"],
			$arParams["PAGER_SHOW_ALWAYS"],
			$this,
			$navComponentParameters
		);
	
	$arResult["NAV_CACHED_DATA"] = null;
	$arResult["NAV_RESULT"] = $rsElement;
	$arResult["NAV_PARAM"] = $navComponentParameters;

	$this->setResultCacheKeys(array(
			"NAV_CACHED_DATA"
		));
	$this->includeComponentTemplate();
}