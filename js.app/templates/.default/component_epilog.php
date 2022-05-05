<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
return;
// пример регистрации \bitrix\modules\main\jscore.php
$pathLibs = $templateFolder.'/libs';
$lstJSLibsConfig = array(
	'app.libs.swiper' => array( //https://swiperjs.com/get-started
		'js' => $pathLibs.'/swiper/swiper-bundle.min.js',
		'css' => $pathLibs.'/swiper/swiper-bundle.min.css'
	),
);

foreach ($lstJSLibsConfig as $ext => $dctExt) {
	\CJSCore::RegisterExt($ext, $dctExt);
}
