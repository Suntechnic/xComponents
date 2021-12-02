<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CBitrixComponent::includeComponentClass("x:x");

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCIbSections extends XC
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
        
        if ($this->arParams['IBLOCK_ID']) $arFilter["IBLOCK_ID"] = $this->arParams['IBLOCK_ID'];
        if ($this->arParams['SECTION_ID']) $arFilter['IBLOCK_SECTION_ID'] = $this->arParams['SECTION_ID'];
        
        return $arFilter;
    }
    
    
    public function executeComponent ()
	{
        
        if($this->startResultCache(
                false
            )) {
            
            if(!\Bitrix\Main\Loader::includeModule('iblock')) {
                $this->abortResultCache();
                ShowError('x:ib.sections - not module');
                return;
            }
            
            
            if ($this->arParams['IBLOCK_ID']) {
                $rsIBlock = \CIBlock::GetList(array(), array(
                        'ACTIVE' => 'Y',
                        'ID' => $this->arParams["IBLOCK_ID"],
                    ));
                $this->arResult['IBLOCK'] = $rsIBlock->GetNext();
            }
            
            
            if ($this->arParams['SECTION_ID']) {
                //TODO: добавить получение данных о родительском разделе
                $this->arResult['SECTION'] = 'не реализовано';
            }
            
            
            //ORDER BY
            $this->arResult['SORT'] = $this->arParams['SORT'];
            // получаем фильтр с учетом дополнительных фильтров
            $this->arResult['FILTER'] = $this->getFilter($this->arParams['FILTERS']);
            // подсчет элементов
            $this->arResult['CNT'] = !!$this->arParams['CNT'];
            //SELECT
            $this->arResult['SELECT'] = $this->arParams['SELECT'];
            
            
            $this->arResult['ITEMS'] = [];
            
            $rsSection = \CIBlockSection::GetList(
                    $this->arResult['SORT'],
                    $this->arResult['FILTER'],
                    $this->arResult['CNT'],
                    $this->arResult['SELECT']
                );
            
            
            while ($arItem = $rsSection->GetNext()) {
                $this->arResult['ITEMS'][] = $arItem;
            }
            unset($arItem);
            
            if (is_array($this->arParams['KEYS_CACHED']) && count($this->arParams['KEYS_CACHED'])) {
                $this->setResultCacheKeys($this->arParams['KEYS_CACHED']);
            }
            
            $this->includeComponentTemplate();
        }
	}
}