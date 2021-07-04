<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CBitrixComponent::includeComponentClass("x:x");

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCHlbList extends XC
{
    
    private $signer;
    
    public function onPrepareComponentParams($arParams)
	{
        $arParams = parent::onPrepareComponentParams($arParams);
        
        $arParams["HLBLOCK_ID"] = intval(trim($arParams["HLBLOCK_ID"]));
        
        if (!is_array($arParams["SELECT"])) $arParams["SELECT"] = ['ID'];
        
        if (!is_array($arParams['FILTER'])) $arParams['FILTER'] = [];
        if (!is_array($arParams['FILTERS'])) $arParams['FILTERS'] = [];
        
        if (!is_array($arParams["SORT"])) $arParams["SORT"] = ['ID'=>'ASC'];
        
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
        
        return $arFilter;
    }
    
    
    public function executeComponent ()
	{
        
        if($this->startResultCache(
                false,
                array($arNavigation)
            )) {
            
            if(!\Bitrix\Main\Loader::includeModule('highloadblock')) {
                $this->abortResultCache();
                ShowError('x:hlb.list - not module');
                return;
            }
            
            
            //ORDER BY
            $this->arResult['SORT'] = $this->arParams['SORT'];
            // получаем фильтр с учетом дополнительных фильтров
            $this->arResult['FILTER'] = $this->getFilter($this->arParams['FILTERS']);
            //SELECT
            $this->arResult['SELECT'] = $this->arParams['SELECT'];
            
            
            $this->arResult['ITEMS'] = [];
            
            
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($this->arParams['HLBLOCK_ID'])->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $EntityClass = $entity->getDataClass();
            
            $res = $EntityClass::getList([
                    'order' => $this->arResult['SORT'],
                    'filter' => $this->arResult['FILTER'],
                    'select' => $this->arResult['SELECT']
                ]);
            
            $lst = [];
            while ($dct = $res->fetch()) $this->arResult['ITEMS'][] = $dct;
            
            if (is_array($this->arParams['KEYS_CACHED']) && count($this->arParams['KEYS_CACHED'])) {
                $this->setResultCacheKeys($this->arParams['KEYS_CACHED']);
            }
            
            
            $this->includeComponentTemplate();
        }
	}
}