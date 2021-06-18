# Библиотека VK API
 
## Установка
```shell
composer require levkopo/vk-api
```

### Примеры использования
**Callback бот**
```php
require_once 'vendor/autoload.php';
use levkopo\VKApi\CallbackApi;

//Ключ доступа сообщества
const VK_ACCESS_TOKEN = "c0223f775444cf3d58a8a1442ec76a9571c8f58e3e24616d9440f73dc43022bbead9b2e576cb41d09c0a1";

//Ключ для подтверждения адреса сервера из настроек Callback API
const VK_CONFIRM_KEY = "d8v2ve07"; 

//Секретный ключ сервера из настроек Callback API
const VK_SECRET_KEY = "secret key";

$vk = CallbackApi::group(VK_ACCESS_TOKEN,
    confirmKey: VK_CONFIRM_KEY,
    secret: VK_SECRET_KEY);
switch ($vk->data['type']){
    case "messages_new": 
        $peerId = $vk->data['object']['message']['text'];
        $vk->sendMessage($peerId, "Hello, World!");
    break;
}
```

