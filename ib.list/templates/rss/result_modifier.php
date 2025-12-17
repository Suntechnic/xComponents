<?php




// соберем DETAIL_PAGE_URL для инфоблока
$arResult['IBLOCK']['IBLOCK_PAGE_URL'] = CIBlock::ReplaceDetailUrl(
        $arResult['IBLOCK']['LIST_PAGE_URL'],
        [
            'IBLOCK_ID' => $arResult['IBLOCK']['ID'],
            'IBLOCK_CODE' => $arResult['IBLOCK']['CODE'],
            'IBLOCK_TYPE_ID' => $arResult['IBLOCK']['IBLOCK_TYPE_ID'],
        ],
        true,
        'E'
    );

// if (!empty($arResult['ITEMS']) && \Bitrix\Main\Loader::includeModule('iblock')) {
//     $arIblock = \Bitrix\Iblock\Iblock::getById($arParams['IBLOCK_ID'])->fetch();
//     $sectionUrlTemplate = $arIblock['SECTION_PAGE_URL'] ?: '';
//     $elementUrlTemplate = $arIblock['DETAIL_PAGE_URL'] ?: '';   
//     foreach ($arResult['ITEMS'] as &$arItem) {
//         $arItem['DETAIL_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
//             $elementUrlTemplate,
//             $arItem,
//             true,
//             'E'
//         );
//         if (isset($arItem['IBLOCK_SECTION_ID']) && $arItem['IBLOCK_SECTION_ID'] > 0) {
//             $arItem['SECTION_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
//                 $sectionUrlTemplate,
//                 $arItem,
//                 true,
//                 'S'
//             );
//         }
//     }
//     unset($arItem);
// }