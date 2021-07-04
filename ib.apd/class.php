<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CBitrixComponent::includeComponentClass("x:x");

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCIbApd extends XC
{
    
    private $signer;
    
    public function onPrepareComponentParams($arParams)
	{
        $arParams = parent::onPrepareComponentParams($arParams);
        
        if(!isset($arParams['FIELDS']) || !count($arParams['FIELDS'])) $arParams['FIELDS'] = ['NAME'];
        if(!isset($arParams['PROPERTIES']) || !count($arParams['FIELDS'])) $arParams['PROPERTIES'] = false;
        
        if(isset($arParams['IBLOCK_ID'])) $arParams['IBLOCK_ID'] = intval(trim($arParams['IBLOCK_ID']));
        
        return $arParams;
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