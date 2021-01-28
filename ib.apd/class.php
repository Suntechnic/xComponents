<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCIbApd extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    
    private $signer;
    
    public function getSalt ()
	{
        if (defined(XDEFINE_SALT)) return 'x_ib_apd_'.XDEFINE_SALT;
        return 'x_ib_apd';
    }
    
    public function getUid ()
	{
        return 'c_'.md5('x:ib.apd '.$this->getTemplateName());
    }
    
    public function onPrepareComponentParams($arParams)
	{
        //if(!isset($arParams['CACHE_TIME'])) $arParams['CACHE_TIME'] = 36000000;
        if(!isset($arParams['FIELDS']) || !count($arParams['FIELDS'])) $arParams['FIELDS'] = ['NAME'];
        if(!isset($arParams['PROPERTIES']) || !count($arParams['FIELDS'])) $arParams['PROPERTIES'] = false;
        if (!$arParams['UID']) $arParams['UID'] = $this->getUid();
        
        if(isset($arParams['IBLOCK_ID'])) $arParams['IBLOCK_ID'] = intval(trim($arParams['IBLOCK_ID']));
        
        return $arParams;
    }
    
    // 
    private function initSigner ()
    {
        $this->signer = new \Bitrix\Main\Security\Sign\Signer;
    }
    
    // подписывает значение 
    public function signVal ($val)
    {
        if (!$this->signer) $this->initSigner();
        $signer = new \Bitrix\Main\Security\Sign\Signer;
        return $signer->sign(base64_encode(serialize($val)),$this->getSalt());
    }
    
    // получает подписанные данные
    // возвращает массив
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
            $signedTemplate,
            $FIELD_VALUES,
            $PROPERTY_VALUES
        )
	{
        $arParams = $this->extractValFromSignedVal($signedParams);
        if ($arParams == null) die('not params');
        $arParams['FIELD_VALUES'] = $FIELD_VALUES;
        $arParams['PROPERTY_VALUES'] = $PROPERTY_VALUES;
        
        
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
    
    
    
    public function executeComponent ()
	{
        
        if($this->arParams['FIELD_VALUES'] && count($this->arParams['FIELD_VALUES'])) {
            $this->apdElement($this->arParams['FIELD_VALUES'],$this->arParams['PROPERTY_VALUES']);
        }
        
        $this->includeComponentTemplate();
	}
    
    
    public function apdElement (
            $arElementFields,
            $arElementProps=false
        )
	{
        
        $result = [];
        
        $arElementFields = array_intersect_key($arElementFields,array_flip($this->arParams['FIELDS']));
        
        
        
        if ($this->arParams['IBLOCK_ID']) {
            if ($arElementFields['IBLOCK_ID'] && $arElementFields['IBLOCK_ID'] != $this->arParams['IBLOCK_ID']) {
                // попытка добавить элемент в другой ИБ - ошибка
                $result = ['STATUS' => 3];
            } else {
                $arElementFields['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
            }
        }
        
        
        
        if ($this->arParams['ID']) {
            if (
                    $arElementFields['ID']
                    && $arElementFields['ID'] != $this->arParams['ID'] // но в данных не совпадает
                ) {
                // попытка обновлить другой элемент - ошибка
                $result = ['STATUS' => 4];
            } else {
                $arElementFields['ID'] = $this->arParams['ID'];
            }
        }
        
        if (!$result['STATUS']) {
            
            if ($arElementProps) {
                $arElementProps = array_intersect_key($arElementProps,array_flip($this->arParams['PROPERTIES']));
                $arElementFields['PROPERTY_VALUES'] = $arElementProps;
            }
            
            $el = new CIBlockElement;
            if ($arElementFields['ID']) {
                $result = ['ACTION' => 'UPDATE', 'STATUS'=>0, 'ID'=>$arElementFields['ID']];
                if($el->Update($arElementFields['ID'],$arElementFields)) {
                    
                } else {
                    $result['STATUS'] = 2; // ошибка обновления
                    $result['ERRORS'] = $el->LAST_ERROR;
                }
            } else {
                $result = ['ACTION' => 'ADD', 'STATUS'=>0];
                if($ID = $el->Add($arElementFields)) {
                    $result['ID'] = $ID;
                } else {
                    $result['STATUS'] = 1; // ошибка добавления
                    $result['ERRORS'] = $el->LAST_ERROR;
                }
            }
        }
        
        
        
        
        //$arElementFields = Array(
        //        'IBLOCK_ID'      => $this->arParams['KEYS_CACHED'],
        //        'NAME'           => $arFields['name'],
        //        'ACTIVE'         => 'Y',            // активен
        //        'PREVIEW_TEXT'   => $arFields['comment'],
        //        'PROPERTY_VALUES'=> array(
        //                'PARTNER_ID' => $arPartners,
        //                'FILE' => CFile::MakeFileArray($arFields['file_path'])
        //            )
        //    );
        
        
        
        $this->arResult['RESULT'] = $result;
        return $this->arResult['RESULT'];
	}
}