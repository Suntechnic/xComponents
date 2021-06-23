<?

$arResult['ITEMS'] = array_map(function ($I) {
        return \Cytamin\Helpers\Article::element_mutate($I);
    },$arResult['ITEMS']);


$arResult['DICTS']['TAGS'] = \Cytamin\Helpers\Article::getTags();