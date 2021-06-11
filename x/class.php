<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XC extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    
    private $signer;
    
    public function getSalt ()
	{
        $strName = str_replace([':','.'],'_',$this->__name);
        if (defined(XDEFINE_SALT)) return $strName.'_'.XDEFINE_SALT;
        return $strName;
    }
    
    public function getUid ()
	{
        return 'c_'.md5($this->__name.'-'.$this->getTemplateName());
    }
    
    public function onPrepareComponentParams(&$arParams)
	{
        if(!isset($arParams["CACHE_TIME"])) $arParams["CACHE_TIME"] = 36000000;
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
    
}