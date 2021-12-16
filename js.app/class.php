<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CBitrixComponent::includeComponentClass("x:x");

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCJsApp extends XC
{
    public function onPrepareComponentParams(&$arParams)
	{
        $arParams = parent::onPrepareComponentParams($arParams);
        if (!is_array($arParams['CONFIG'])) $arParams['CONFIG'] = [];
        return $arParams;
    }
    
    public function executeComponent ()
	{
        
        $dctConfig = $this->arParams['CONFIG'];
        if (!isset($dctConfig['lang'])) $dctConfig['lang'] = LANGUAGE_ID;
        if (!isset($dctConfig['env']) && defined('APPLICATION_ENV')) $dctConfig['env'] = APPLICATION_ENV;
        if (!isset($dctConfig['cookie_name'])) {
            $dctConfig['cookie_name'] = \Bitrix\Main\Config\Option::get('main', 'cookie_name', 'BITRIX_SM');
        }
        
        $this->arResult['CONFIG'] = $dctConfig;
        
        $this->includeComponentTemplate();
        
        if (is_array($this->arParams['EXTENSIONS'])) {
            foreach ($this->arParams['EXTENSIONS'] as $ext) \Bitrix\Main\UI\Extension::load($ext);
        }
        
        if (is_array($this->arParams['SCRIPTS'])) {
            $asset = \Bitrix\Main\Page\Asset::getInstance();
            foreach ($this->arParams['SCRIPTS'] as $script) $asset->addJS($script);
        }
	}
}