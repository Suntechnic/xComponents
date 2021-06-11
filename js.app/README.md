Типичный вызов
==============

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

Использование
=============

Если мобильный вид:
```js
if (APP.env.is_mobileView())
```

Если обычный вид:
```js
if (APP.env.is_desktopView())
```

Отправка формы:
```html
<form action="<?=P_INTERFACE?>/feedback-reciever.php" onsubmit="var form = this; APP.Util.submitForm(form,(responce)=>{form.closest('div').innerHTML = responce}); return false;">
```

Маска ввода телефона:
```html
<input
        type="tel"
        name="PROPERTY_VALUES[PHONE]"
        placeholder="+7 (795) 555-2525"
        maxlength="17"
        value=""
        onfocus="APP.Util.maskingInput(this,'\\+7 \\(\\d{3}\\) \\d{3}\\-\\d{4}','+7 (___) ___-____'); this.removeAttribute('onfocus');"
        required
    >
```