<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CBitrixComponent::includeComponentClass("x:x");

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCIbList extends XC
{
    
    private $signer;
    
    public function onPrepareComponentParams(&$arParams)
	{
        $arParams = parent::onPrepareComponentParams($arParams);
        
        $arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);
        
        if (!is_array($arParams["SELECT"])) {
            $arParams["SELECT"] = ['ID','IBLOCK_ID'];
        } else {
            if (!in_array('ID',$arParams["SELECT"])) $arParams["SELECT"][] = 'ID';
            if (!in_array('IBLOCK_ID',$arParams["SELECT"])) $arParams["SELECT"][] = 'IBLOCK_ID';
        }
        
        if (!is_array($arParams['FILTER'])) $arParams['FILTER'] = [];
        if (!is_array($arParams['FILTERS'])) $arParams['FILTERS'] = [];
        
        if (!is_array($arParams["SORT"])) $arParams["SORT"] = ['ID'=>'ASC'];
        
        $arParams['ELEMENTS_COUNT'] = intval($arParams['ELEMENTS_COUNT']);
        if($arParams['ELEMENTS_COUNT'] <= 0) {
            $arParams['ELEMENTS_COUNT'] = 0;
            if ($arParams['PAGER']) $arParams['ELEMENTS_COUNT'] = 20;
        }
        
        //if (!is_array($arParams['KEYS_CACHED'])) $arParams['KEYS_CACHED'] = array(
        //        "NAV_CACHED_DATA"
        //    );
        
        return $arParams;
    }
    
    // возвращает фильтр компонента применив к нему дополнительные фильтры из $arFilters
    public function getFilter ($arFilters=[])
	{
        $arFilter = $this->arParams['FILTER'];
        foreach ($arFilters as $additionalFilter) {
            if (is_array($additionalFilter)) {
                $arFilter = array_merge($arFilter,$additionalFilter);
            }
        }
        
        if ($this->arParams['IBLOCK_ID']) $arFilter["IBLOCK_ID"] = $this->arParams["IBLOCK_ID"];
        if ($this->arParams['SECTION_ID']) $arFilter['SECTION_ID'] = $this->arParams['SECTION_ID'];
        if ($this->arParams['SECTION_CODE']) $arFilter['SECTION_CODE'] = $this->arParams['SECTION_CODE'];
        
        return $arFilter;
    }
    
    
    public function executeComponent ()
	{
        
        \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        
        
        $arNavParams = false;
        if(is_array($this->arParams['PAGER'])) {
            $arNavParams = array(
                    'nPageSize' => $this->arParams['ELEMENTS_COUNT'],
                    'bDescPageNumbering' => false,
                    'bShowAll' => $this->arParams['PAGER']['SHOW_ALL'],
                );
            if ($this->arParams['PAGER']['PAGE']) {
                $arNavParams['iNumPage'] = $this->arParams['PAGER']['PAGE'];
            }
            $arNavigation = \CDBResult::GetNavParams($arNavParams);
        } else {
            if ($this->arParams['ELEMENTS_COUNT']) $arNavParams = array(
                    'nTopCount' => $this->arParams['ELEMENTS_COUNT'],
                    'bDescPageNumbering' => false,
                );
            $arNavigation = false;
        }
    
        
        if($this->startResultCache(
                false,
                array($arNavigation)
            )) {
            
            if(!\Bitrix\Main\Loader::includeModule('iblock')) {
                $this->abortResultCache();
                ShowError('x:ib.list - not module');
                return;
            }
            
            // добавление информации об ИБ
            if ($this->arParams['IBLOCK_ID']) {
                $rsIBlock = \CIBlock::GetList(array(), array(
                        'ACTIVE' => 'Y',
                        'ID' => $this->arParams['IBLOCK_ID'],
                    ));
                $this->arResult['IBLOCK'] = $rsIBlock->GetNext();
            }
            
            // добавление информации об разделе
            if ($this->arParams['SECTION_ID'] || $this->arParams['SECTION_CODE']) {
                # http://dev.1c-bitrix.ru/api_help/iblock/classes/ciblocksection/getlist.php
                $arSectionFilter = [
                        'ACTIVE'=>'Y'
                    ];
                if ($this->arParams['IBLOCK_ID']) $arSectionFilter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
                if ($this->arParams['SECTION_ID']) $arSectionFilter['ID'] = $this->arParams['SECTION_ID'];
                if ($this->arParams['SECTION_CODE']) $arSectionFilter['CODE'] = $this->arParams['SECTION_CODE'];
                
                $rsSection = CIBlockSection::GetList(
                        [],
                        $arFilter
                    );
                $this->arResult['SECTION'] = $rsSection->fetch();
            }
            
            
            //ORDER BY
            $this->arResult['SORT'] = $this->arParams['SORT'];
            // получаем фильтр с учетом дополнительных фильтров
            $this->arResult['FILTER'] = $this->getFilter($this->arParams['FILTERS']);
            // параметры навигацияя
            $this->arResult['NAV'] = $arNavParams;
            //SELECT
            $this->arResult['SELECT'] = $this->arParams['SELECT'];
            
            
            $this->arResult['ITEMS'] = [];
            
            $rsElement = \CIBlockElement::GetList(
                    $this->arResult['SORT'],
                    $this->arResult['FILTER'],
                    false,
                    $this->arResult['NAV'],
                    $this->arResult['SELECT']
                );
            
            while ($arItem = $rsElement->GetNext()) {
                $this->arResult['ITEMS'][] = $arItem;
            }
            unset($arItem);
            
            if (is_array($this->arParams['PAGER'])) {
                $navComponentParameters = array();
                
                $this->arResult['NAV_STRING'] = $rsElement->GetPageNavStringEx(
                        $navComponentObject,
                        $this->arParams['PAGER']['TITLE'],
                        $this->arParams['PAGER']['TEMPLATE'],
                        $this->arParams['PAGER']['SHOW_ALWAYS']=='Y'?true:false,
                        $this,
                        $navComponentParameters
                    );
                
                $this->arResult['NAV_CACHED_DATA'] = null;
                $this->arResult['NAV_RESULT'] = $rsElement;
                $this->arResult['NAV_PARAM'] = $navComponentParameters;
            }
            
            if (is_array($this->arParams['KEYS_CACHED']) && count($this->arParams['KEYS_CACHED'])) {
                $this->setResultCacheKeys($this->arParams['KEYS_CACHED']);
            }
            
            $this->includeComponentTemplate();
        }
	}
}
