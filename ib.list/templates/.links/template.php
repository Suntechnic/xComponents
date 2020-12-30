<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<ul>
	<?foreach ($arResult['ITEMS'] as $arItem):?>
	<li data-bxelmid="<?=$arItem['ID']?>">
		<?if ($arItem['DETAIL_PAGE_URL']):?><a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?endif?>
		<?=$arItem['NAME']?$arItem['NAME']:$arItem['ID']?>
		<?if ($arItem['DETAIL_PAGE_URL']):?></a><?endif?>
	</li>
	<?endforeach?>
</ul>

<?=$arResult['NAV_STRING']?>