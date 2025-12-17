<rss 
   xmlns:yandex="http://news.yandex.ru" 
   xmlns:media="http://search.yahoo.com/mrss/"
   version="2.0">
    <title><?=$arParams['TEMPLATE']['TAGS']['title']?$arParams['TEMPLATE']['TAGS']['title']:$arResult['IBLOCK']['NAME']?></title>
    <link>//<?=SITE_SERVER_NAME?><?=$arParams['TEMPLATE']['TAGS']['link']?$arParams['TEMPLATE']['TAGS']['link']:$arResult['IBLOCK']['IBLOCK_PAGE_URL']?></link>
    <description><![CDATA[<?=$arParams['TEMPLATE']['TAGS']['description']?$arParams['TEMPLATE']['TAGS']['description']:$arResult['IBLOCK']['DESCRIPTION']?>]]></description>
    <?if($arParams['TEMPLATE']['TAGS']['language']):?><language>ru</language><?endif?>
    <lastBuildDate><?=date(DATE_RSS)?></lastBuildDate>
    <?foreach($arResult['ITEMS'] as $arItem):?>
        <item>
            <title><?=$arItem['NAME']?></title>
            <link>//<?=SITE_SERVER_NAME?><?=$arItem['DETAIL_PAGE_URL']?></link>
            <?if($arItem['PREVIEW_TEXT']):?>
            <description><![CDATA[<?=$arItem['PREVIEW_TEXT']?>]]></description>
            <?endif?>
            <?if($arItem['DETAIL_TEXT']):?>
            <yandex:full-text><![CDATA[<?=$arItem['DETAIL_TEXT']?>]]></yandex:full-text>
            <?endif?>
            <pubDate><?=date(DATE_RSS, strtotime($arItem['ACTIVE_FROM']))?></pubDate>
            <guid isPermaLink="false"><?=$arItem['ID']?></guid>
        </item>
    <?endforeach?>
</rss>