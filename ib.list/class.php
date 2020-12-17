<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCIbList extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    
    private $signer;
    
    public function getSalt ()
	{
        if (defined(XDEFINE_SALT)) return 'x_ib_list_'.XDEFINE_SALT;
        return 'x_ib_list';
    }
    
    public function getUid ()
	{
        return 'c_'.md5('x:ib.list '.$this->getTemplateName());
    }
    
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
        
        if (!is_array($arParams['FILTER'])) $arParams['FILTER'] = [];
        if (!is_array($arParams['FILTERS'])) $arParams['FILTERS'] = [];
        
        if (!is_array($arParams["SORT"])) $arParams["SORT"] = ['ID'=>'ASC'];
        
        $arParams['ELEMENTS_COUNT'] = intval($arParams['ELEMENTS_COUNT']);
        if($arParams['ELEMENTS_COUNT']<=0) $arParams['ELEMENTS_COUNT'] = 20;
        
        //if (!is_array($arParams['KEYS_CACHED'])) $arParams['KEYS_CACHED'] = array(
        //        "NAV_CACHED_DATA"
        //    );
        
        
        if (!$arParams['UID']) $arParams['UID'] = $this->getUid();
        
        return $arParams;
    }
    
    private function initSigner ()
    {
        $this->signer = new \Bitrix\Main\Security\Sign\Signer;
    }
    
    public function signVal ($val)
    {
        if (!$this->signer) $this->initSigner();
        $signer = new \Bitrix\Main\Security\Sign\Signer;
        return $signer->sign(base64_encode(serialize($val)),$this->getSalt());
    }
    
    
    public function extractValFromSignedVal ($signedVal)
    {
        $debrisSignedVal = explode('.',$signedVal);
        if (!$this->signer) $this->initSigner();
        if ($this->signer->validate($debrisSignedVal[0], $debrisSignedVal[1], $this->getSalt())) {
            return unserialize(base64_decode($debrisSignedVal[0]));
        } else {
            return null;
        }
        
    }
    
    public function executeAction (
            $signedParams, // подписанные параметры
            $signedParamsMutation=false, // подписанные массив мутаций параметров (каждый ключ заменит аналогичный в Params)
            $signedTemplate // подписанный шаблон
        )
	{
        $arParams = $this->extractValFromSignedVal($signedParams);
        if ($arParams == null) die('not params'); 
        if ($signedParamsMutation) {
            $arParamsMutation = $this->extractValFromSignedVal($signedParamsMutation);
            if ($arParamsMutation != null) {
                foreach ($arParamsMutation as $key=>$val) {
                    $arParams[$key] = $val;
                }
            } else {
                die();
            }
        }
        $template = $this->extractValFromSignedVal($signedTemplate);
        if ($template != null) {
            $this->arParams = $arParams;
            $this->setTemplateName($template);
            
            $this->executeComponent();
            die();
        } else {
            die('not template');
        }
    }
    
    public function configureActions ()
	{
		return [
			'execute' => [
				'prefilters' => [],
				'postfilters' => []
			]
		];
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
        
        return $arFilter;
    }
    
    
    public function executeComponent ()
	{
        
        \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        
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
            $arNavParams = array(
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
            
            
            if ($this->arParams['IBLOCK_ID']) {
                $rsIBlock = \CIBlock::GetList(array(), array(
                        "ACTIVE" => "Y",
                        "ID" => $this->arParams["IBLOCK_ID"],
                    ));
                $this->arResult['IBLOCK'] = $rsIBlock->GetNext();
            }
            
            
            if ($this->arParams['SECTION_ID']) {
                //TODO: добавить получение данных о разделе
                $this->arResult['SECTION'] = 'не реализовано';
            }
            
            //SELECT
            $arSelect = $this->arParams['SELECT'];
        
            // получаем фильтр с учетом дополнительных фильтров
            $this->arResult['FILTER'] = $this->getFilter($this->arParams['FILTERS']);
            
            //ORDER BY
            $arSort = $this->arParams['SORT'];
            
            $this->arResult['ITEMS'] = [];
            
            $rsElement = \CIBlockElement::GetList(
                    $arSort,
                    $this->arResult['FILTER'],
                    false,
                    $arNavParams,
                    $arSelect
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
                        $this->arParams['PAGER']['SHOW_ALWAYS'],
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