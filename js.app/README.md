Типичный вызов

```
<?
$lstScripts = [
        P_JS.'/vendor.js'
    ];
if (APPLICATION_ENV == 'dev') {
    $lstScripts[] = '/local/sources/src/js'.'/80.plagins.js';
    $lstScripts[] = '/local/sources/src/js'.'/99.main.js';
} else {
    $lstScripts[] = P_JS.'/app.js';
}
?>

<?$APPLICATION->IncludeComponent(
        'x:js.app',
        '',
        Array(
                'CONFIG' => ['mobileMaxWidth' => 760],
                'SCRIPTS' => $lstScripts,
            )
    );?>
```

