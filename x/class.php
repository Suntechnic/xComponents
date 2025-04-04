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
        if (defined('XDEFINE_SALT')) return $strName.'_'.XDEFINE_SALT;
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
    public function onPrepareComponentParams ($arParams)
	{
        if(!isset($arParams['CACHE_TIME'])) $arParams['CACHE_TIME'] = 86399;
        if (!$arParams['UID']) $arParams['UID'] = $this->getUid();
        if (!$arParams['LANGUAGE_ID']) $arParams['LANGUAGE_ID'] = LANGUAGE_ID;
        //if (!$arParams['SITE_ID']) $arParams['SITE_ID'] = SITE_ID;

        if (!$arParams['DEBUG']) { // еcли DEBUG не стоит извне
            $DebugParam = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('debug');
            if (
                    $DebugParam // дебаг установлен
                    && defined('APPLICATION_ENV') // и установлено окружени
                    && APPLICATION_ENV != 'production' // и это не продакшен
                ) {
                global $USER;
                if ($USER->isAdmin()) { // и пользователей админ
                    $arParams['CACHE_TYPE'] = 'N';
                    $arParams['CACHE_TIME'] = 0;
                    $arParams['DEBUG'] = $DebugParam;
                }
            }
            
        }
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
            return unserialize(base64_decode($debrisSignedVal[0]), ['allowed_classes' => false]);
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
    
    public $arParamsTemplate;
    public function executeComponent ()
	{
        // добавляем $arParamsTemplate
        if ($this->arParams['TEMPLATE'] && is_array($this->arParams['TEMPLATE'])) {
            $this->arParamsTemplate = $this->arParams['TEMPLATE'];
        } else $this->arParamsTemplate = [];
        

        if($this->startResultCache(
                false
            )) {
            $this->arResult = $this->getParams(true);
            $this->includeComponentTemplate();
        } else {

        }
	}
    
    /*
    * возвращает параметры компонента очищенные от исходных значений (с ~)
    * либо оригинальные, не зажоплинные параметры, если $Origin==true
    */
    private $_arParams_final;
    private $_arParams_origin;
    public function getParams (bool $Origin=false): array
	{

        if (!$this->_arParams_final) {
            $this->_arParams_final = [];
            $this->_arParams_origin = [];
            foreach ($this->arParams as $Key=>$Val) {
                if ('~' == substr($Key,0,1)) {
                    $this->_arParams_origin[substr($Key,1)] = $Val;
                } else {
                    $this->_arParams_final[$Key] = $Val;
                }
            }

            // параметры при ajax запросе могут быть не засраны,
            // тогда нужно это учесть и перенести в _arParams_origin ключи из _arParams_final
            if (count($this->_arParams_final) > count($this->_arParams_origin)) {
                foreach ($this->_arParams_final as $Key=>$Val) {
                    if (!array_key_exists($Key,$this->_arParams_origin)) $this->_arParams_origin[$Key] = $Val;
                }
            }
            
        }

        //\Kint::dump($this->arParams, $this->_arParams_origin,$this->_arParams_final); die();

        if ($Origin) {
            return $this->_arParams_origin;
        } else {
            return $this->_arParams_final;
        }
    }
    
    
    /*
    * функции работы с параметрами компонентов сохраняемыми в БД
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



    /*
    * получает на вход массив битрикс-засранных массивов
    * и удаляет из них срань
    * например $arResult['ITEMS'] = \XC::cleanArrays($arResult['ITEMS']);
    * или $arResult['ITEMS'] = \XC::cleanArrays($arResult['ITEMS'],\XC::DIRTING_PREFIXES, \XC::DIRTING_POSTFIXES);
    * сохраянет ключи
    */
    const DIRTING_PREFIXES = ['~'];
    const DIRTING_POSTFIXES = ['_VALUE_ID','_ENUM_ID'];
    public static function cleanArrays (
            array $refArDirty, 
            array $lstPrefix = ['~'], // список грязных префиксов
            array $lstPostfix = [] // список грязных постфиксов
        ): array
	{
        $refArCleaned = [];

        $lener = function ($s) {
            return strlen($s);
        };

        if (count($lstPrefix)) {
            $refPrefix = array_combine($lstPrefix, array_map($lener,$lstPrefix));
        } else $refPrefix = [];

        if (count($lstPostfix)) {
            $refPostfix = array_combine($lstPostfix, array_map($lener,$lstPostfix));
        } else $refPostfix = [];


        foreach ($refArDirty as $Key=>$arVal) {
            if (is_array($arVal)) {
                $refArCleaned[$Key] = self::cleanArrayByRef($arVal,$refPrefix,$refPostfix);
            } else {
                $refArCleaned[$Key] = $arVal;
            }
        }
        
        return $refArCleaned;
    }
    public static function cleanArray (
            array $arDirty, 
            array $lstPrefix = ['~'], // список грязных префиксов
            array $lstPostfix = [] // список грязных постфиксов
        ): array
    {
        return self::cleanArrays([$arDirty],$lstPrefix,$lstPostfix)[0];
    }

    private static function cleanArrayByRef (
            array $arDirty, 
            array $refPrefix, // справочник префикс=>длина
            array $refPostfix // справочник постфикс=>длина
        ): array
    {
        $arCleaned = [];

        $arCleaned = array_filter($arDirty, function ($Key) use ($refPrefix,$refPostfix) {
                foreach ($refPrefix as $Prefix => $Len) {
                    if ($Prefix == substr($Key,0,$Len)) return false;
                }
                foreach ($refPostfix as $Postfix => $Len) {
                    if ($Postfix == substr($Key,$Len*-1)) return false;
                }
                return true;
            }, ARRAY_FILTER_USE_KEY);
        
        return $arCleaned;
    }


}
