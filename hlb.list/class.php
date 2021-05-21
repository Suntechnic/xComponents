<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCHlbList extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    
    private $signer;
    
    public function getSalt ()
	{
        if (defined(XDEFINE_SALT)) return 'x_hlb_list_'.XDEFINE_SALT;
        return 'x_hlb_list';
    }
    
    public function getUid ()
	{
        return 'c_'.md5('x:hlb.list '.$this->getTemplateName());
    }
    
    public function onPrepareComponentParams($arParams)
	{
        if(!isset($arParams["CACHE_TIME"])) $arParams["CACHE_TIME"] = 36000000;
        
        $arParams["HLBLOCK_ID"] = intval(trim($arParams["HLBLOCK_ID"]));
        
        if (!is_array($arParams["SELECT"])) $arParams["SELECT"] = ['ID'];
        
        if (!is_array($arParams['FILTER'])) $arParams['FILTER'] = [];
        if (!is_array($arParams['FILTERS'])) $arParams['FILTERS'] = [];
        
        if (!is_array($arParams["SORT"])) $arParams["SORT"] = ['ID'=>'ASC'];
        
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
    
    // возвращает параметры компонента очищенные от исходных значений (с ~)
    public function getParams ()
	{
        if (!$this->_arParams_final) {
            $this->_arParams_final = [];
            foreach ($this->arParams as $key=>$val) {
                if ('~' == substr($key,0,1)) continue;
                $this->_arParams_final[$key] = $val;
            }
        }
        return $this->_arParams_final;
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