<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\CBitrixComponent::includeComponentClass("x:x");

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
class XCInstagramPosts extends XC
{
    
    private $signer;
    
    public function onPrepareComponentParams(&$arParams)
	{
        $arParams = parent::onPrepareComponentParams($arParams);
        
        $arParams['ELEMENTS_COUNT'] = intval($arParams['ELEMENTS_COUNT']);
        if (!$arParams['ELEMENTS_COUNT']) $arParams['ELEMENTS_COUNT'] = 8;
        
        
        return $arParams;
    }
    

    
    public function executeComponent ()
	{
        
        
        
        //$url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=" . $accessToken;
        //
        //$instagramCnct = curl_init(); // инициализация cURL подключения
        //curl_setopt($instagramCnct, CURLOPT_URL, $url); // адрес запроса
        //curl_setopt($instagramCnct, CURLOPT_RETURNTRANSFER, 1); // просим вернуть результат
        //$response = json_decode(curl_exec($instagramCnct)); // получаем и декодируем данные из JSON
        //curl_close($instagramCnct); // закрываем соединение
        //
        //// обновляем токен и дату его создания в базе
        //$accessToken = $response->access_token; // обновленный токен
        //
        //$url = "https://graph.instagram.com/me/media?fields=id,media_type,media_url,caption,timestamp,thumbnail_url,permalink&access_token=" . $accessToken;
        //$instagramCnct = curl_init(); // инициализация cURL подключения
        //curl_setopt($instagramCnct, CURLOPT_URL, $url); // адрес запроса
        //curl_setopt($instagramCnct, CURLOPT_RETURNTRANSFER, 1); // просим вернуть результат
        //$media = json_decode(curl_exec($instagramCnct)); // получаем и декодируем данные из JSON
        //curl_close($instagramCnct); // закрываем соединение
        
        
        $accessToken = $this->getOption('token'); // получаем токен из базы
        $tokenDate = $this->getOption('date'); // получаем дату создания из базы
        if (!$accessToken) { // токена в БД еще нет
            // возьмем его из параметров и азпишем в БД
            $accessToken = $this->arParams['TOKEN'];
            $tokenDate = time() - 1;
            
            $this->setOption('token', $accessToken); 
            $this->setOption('date', $tokenDate);
        }
        
        // Вычисляем сколько полных дней прошло с даты создания токена
        $tokenTimestamp = strtotime($tokenDate);
        $curTimestamp = time();
        $dayDiff = ($curTimestamp - $tokenTimestamp) / 86400;
        
        if (!empty($accessToken)) {
            if ($dayDiff > 50) { // Если токену уже более 50 дней, то обновляем его
          
                // Запрос на обновление токена
                $url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=" . $accessToken;
                $instagramCnct = curl_init(); // инициализация cURL подключения
                curl_setopt($instagramCnct, CURLOPT_URL, $url); // адрес запроса
                curl_setopt($instagramCnct, CURLOPT_RETURNTRANSFER, 1); // просим вернуть результат
                $response = json_decode(curl_exec($instagramCnct)); // получаем и декодируем данные из JSON
                curl_close($instagramCnct); // закрываем соединение
                
                
                // обновляем токен и дату его создания в базe
                $accessToken = $response->access_token; // обновленный токен
                $tokenDate = time();
                
                $this->setOption('token', $accessToken);
                $this->setOption('date', $tokenDate);
            }
          
            // Получаем ленту
            
            $url = 'https://graph.instagram.com/me/media?fields=id,media_type,media_url,caption,timestamp,thumbnail_url,permalink,children{fields=id,media_url,thumbnail_url,permalink}&limit='.$this->arParams['ELEMENTS_COUNT'].'&access_token=' . $accessToken;
            $instagramCnct = curl_init(); // инициализация cURL подключения
            curl_setopt($instagramCnct, CURLOPT_URL, $url); // подключаемся
            curl_setopt($instagramCnct, CURLOPT_RETURNTRANSFER, 1); // просим вернуть результат
            $media = json_decode(curl_exec($instagramCnct)); // получаем и декодируем данные из JSON
            curl_close($instagramCnct); // закрываем соединение
          
            $instaFeed = array();
            foreach ($media->data as $mediaObj) {
                $dctItem['ID'] = $mediaObj->id;
                $dctItem['IMG'] = $mediaObj->thumbnail_url ?: $mediaObj->media_url;
                $dctItem['LINK'] = $mediaObj->permalink;
                $dctItem['CAPTION'] = $mediaObj->caption;
                $dctItem['MEDIA_TYPE'] = $mediaObj->media_type;
                $dctItem['TIMESTAMP'] = $mediaObj->timestamp;
                
                if (!empty($mediaObj->children->data)) {
                    foreach ($mediaObj->children->data as $children) {
                        $dctItem['CHILDREN'][$children->id]['IMG'] = $children->thumbnail_url ?: $children->media_url;
                        $dctItem['CHILDREN'][$children->id]['LINK'] = $children->permalink;
                    }
                }
                
                $instaFeed[] = $dctItem;
            }
            
            $this->arResult['ITEMS'] = $instaFeed;
        }
        
        if($this->startResultCache(
                false
            )) {
            $this->includeComponentTemplate();
        }
	}
}