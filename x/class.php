<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XC extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    
    private $signer;
    
    /*
     * Возвращает соль для signer'а
    */
    public function getSalt ()
	{
        $strName = str_replace([':','.'],'_',$this->__name);
        if (defined(XDEFINE_SALT)) return $strName.'_'.XDEFINE_SALT;
        return $strName;
    }
    
    /*
     * Возвращает UID компонента
     * пока не уникально на основе имени и шаблона
    */
    public function getUid ()
	{
        return 'c_'.md5($this->__name.'-'.$this->getTemplateName());
    }
    
    /*
     * Подготавливает параметры компонента добавляя обязательные недостающие
     * 
    */
    public function onPrepareComponentParams(&$arParams)
	{
        if(!isset($arParams["CACHE_TIME"])) $arParams["CACHE_TIME"] = 36000000;
        if (!$arParams['UID']) $arParams['UID'] = $this->getUid();
        if (!$arParams['LANGUAGE_ID']) $arParams['LANGUAGE_ID'] = LANGUAGE_ID;
        //if (!$arParams['SITE_ID']) $arParams['SITE_ID'] = SITE_ID;
        return $arParams;
    }
    
    /*
     * иницицализирует signer
    */
    private function initSigner ()
    {
        $this->signer = new \Bitrix\Main\Security\Sign\Signer;
    }
    
    /*
     * Подписывает переданный параметр - необходимо в шаблоне для формирования запросов
    */
    public function signVal ($val)
    {
        if (!$this->signer) $this->initSigner();
        $signer = new \Bitrix\Main\Security\Sign\Signer;
        return $signer->sign(base64_encode(serialize($val)),$this->getSalt());
    }
    
    /*
    * Воставнавливает подписанное значение
    * 
    */
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
    
    /*
    * Выполняется компонент при ajax запросе
    * 
    */
    public function executeAction (
            $signedParams, // подписанные параметры
            $signedParamsMutation=false, // подписанный массив мутаций параметров (каждый ключ заменит аналогичный в Params)
            $signedTemplate, // подписанный шаблон
            $ajaxParams=false // любые параметры добавляемые js - будут переданы в ключе
        )
	{
        
        // востанавливает параметры
        $arParams = $this->extractValFromSignedVal($signedParams);
        if ($arParams == null) die('not params');
        
        // если имеется патч параметров
        if ($signedParamsMutation) { 
            // востанавливает его
            $arParamsMutation = $this->extractValFromSignedVal($signedParamsMutation);
            if ($arParamsMutation != null) {
                // и применяет к массиву праметров
                foreach ($arParamsMutation as $key=>$val) {
                    $arParams[$key] = $val;
                }
            } else {
                die('invalid params mutation');
            }
        }
        
        // если есть ajax параметры
        if ($ajaxParams) {
            $arParams['AJAX_PARAMS'] = $ajaxParams;
        }
        
        // востанавливает шаблон
        $template = $this->extractValFromSignedVal($signedTemplate);
        
        // выполняем компонент
        if ($template != null) {
            $this->arParams = $arParams;
            $this->setTemplateName($template);
            
            $this->executeComponent();
            die();
        } else {
            die('not template');
        }
    }
    
    // конфигурация выполняения по ajax
    public function configureActions ()
	{
		return [
			'execute' => [
				'prefilters' => [],
				'postfilters' => []
			]
		];
	}
    
    public function executeComponent ()
	{
        
        if($this->startResultCache(
                false
            )) {
            $this->includeComponentTemplate();
        }
	}
    
    /*
    * возвращает параметры компонента очищенные от исходных значений (с ~)
    * просту удаляет ключи с ~
    */
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
    
    
    /*
    * сохраняет параметры
    */
    public function getOption ($name)
	{
        return \Bitrix\Main\Config\Option::get(
                str_replace(':','__',$this->__name),
                $name,
                null,
                SITE_ID
            );
    }
    public function setOption ($name,$value)
	{
        return \Bitrix\Main\Config\Option::set(
                str_replace(':','__',$this->__name),
                $name,
                $value,
                SITE_ID
            );
    }
}