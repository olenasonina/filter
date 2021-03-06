<?php
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

//Подключение к странице

$cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
$api = new Api($cachePool);
$username = Data::getLogin();
$password = Data::getPass();
$api->login($username, $password);

//Получение первых 12 фото, если они выложены за последние сутки

$profile = $api->getProfile($username);
$first_part = printMedias($profile->getMedias());

//Получение еще 36 фото, если они выложены за последние сутки

for($i=0; $i<3; $i++) {
    $profile = $api->getMoreMedias($profile);
    $second_part = printMedias($profile->getMedias());  
}

//Получение массива данных

$urls = getURL($first_part, $second_part);
krsort($urls);

//Получение данных из БД и исключение из массива дублей

$bd_data = SelectDataFromBD::select("SELECT ins_time FROM insta");
foreach($urls as $key => $url) {
    if(in_array($url['date'], $bd_data)) {
        unset($urls[$key]);
    } 
} 


//Запись новых данных в БД

if(count($urls)>0) {
    InsertDataToBD::insert($urls);
}

//Функции

function getURL($first_part, $second_part) {
    $photos_url = array_merge($first_part, $second_part);
    return $photos_url;
}


function printMedias(array $medias)
{
    $albom = [];
    foreach ($medias as $key => $media) {
        if($media->getDate()->format('U') > (time()-(6*24*60*60))) {
            $albom[$key]['url'] = $media->getThumbnailSrc();
            $albom[$key]['date'] = $media->getDate()->format('Y-m-d H:i:s');
        }
       
    }
    return $albom;
}
