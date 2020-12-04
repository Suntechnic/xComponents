<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XIBList extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
	{
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
        
        if (!is_array($arParams['KEYS_CACHED'])) $arParams['KEYS_CACHED'] = array(
                "NAV_CACHED_DATA"
            );
        
        
        
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
                
        return $arParams;
    }
    
    public function executeComponent()
	{
		//global $APPLICATION;
		//
		//$this->setFrameMode(false);
		//$this->context = Main\Application::getInstance()->getContext();
		//$this->checkSession = $this->arParams["DELIVERY_NO_SESSION"] == "N" || check_bitrix_sessid();
		//$this->isRequestViaAjax = $this->request->isPost() && $this->request->get('via_ajax') == 'Y';
		//$isAjaxRequest = $this->request["is_ajax_post"] == "Y";
		//
		//if ($isAjaxRequest)
		//	$APPLICATION->RestartBuffer();
		//
		//$this->action = $this->prepareAction();
		//Sale\Compatible\DiscountCompatibility::stopUsageCompatible();
		//$this->doAction($this->action);
		//Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
		//
		//if (!$isAjaxRequest)
		//{
		//	CJSCore::Init(['fx', 'popup', 'window', 'ajax', 'date']);
		//}
		//
		////is included in all cases for old template
		//$this->includeComponentTemplate();
		//
		//if ($isAjaxRequest)
		//{
		//	$APPLICATION->FinalActions();
		//	die();
		//}
        
        $pagerParameters = array();

        if($this->startResultCache(
                false,
                array(
                        $arNavigation,
                        $arrFilter,
                        $pagerParameters
                    )
            )) {
            
            if(!\Bitrix\Main\Loader::includeModule('iblock')) {
                $this->abortResultCache();
                ShowError('x:ib.list - not module');
                return;
            }
            
        
            $rsIBlock = CIBlock::GetList(array(), array(
                    "ACTIVE" => "Y",
                    "ID" => $this->arParams["IBLOCK_ID"],
                ));
        
        
            $this->arResult = $rsIBlock->GetNext();
            if (!$this->arResult) {
                $this->abortResultCache();
                return;
            }
            
            //SELECT
            $arSelect = $this->arParams["SELECT"];
        
            //WHERE
            $arFilter = $this->arParams["FILTER"];
            $arFilter["IBLOCK_ID"] = $this->arParams["IBLOCK_ID"];
        
            //ORDER BY
            $arSort = $this->arParams["SORT"];
        
            $this->arResult["ITEMS"] = array();
            $this->arResult["ELEMENTS"] = array();
            $rsElement = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);
            while ($arItem = $rsElement->GetNext()) {
                
                //$ipropValues = new Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
                //$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();
                
                if (count($this->arParams['KEYS_GETFILE'])) {
                    \Bitrix\Iblock\Component\Tools::getFieldImageData(
                            $arItem,
                            $this->arParams['KEYS_GETFILE'],
                            \Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT
                        );
                }
                
                
                
                $this->arResult["ITEMS"][] = $arItem;
            }
            unset($arItem);
        
            $navComponentParameters = array();
        
            $this->arResult["NAV_STRING"] = $rsElement->GetPageNavStringEx(
                    $navComponentObject,
                    $this->arParams["PAGER_TITLE"],
                    $this->arParams["PAGER_TEMPLATE"],
                    $this->arParams["PAGER_SHOW_ALWAYS"],
                    $this,
                    $navComponentParameters
                );
            
            
        
            
            $this->arResult["NAV_CACHED_DATA"] = null;
            $this->arResult["NAV_RESULT"] = $rsElement;
            $this->arResult["NAV_PARAM"] = $navComponentParameters;
        
            $this->setResultCacheKeys($this->arParams['KEYS_CACHED']);
            
            $this->includeComponentTemplate();
        }
	}
}