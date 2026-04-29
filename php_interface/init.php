<?php


\Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Dev\Site\Handlers\Iblock' => '/local/modules/dev.site/lib/Handlers/Iblock.php',
]);

\Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Dev\Site\Helpers\Iblock' => '/local/modules/dev.site/lib/Helpers/Iblock.php',
]);



use Bitrix\Main\EventManager;
use Dev\Site\Handlers\Iblock;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    [Iblock::class, 'OnAfterIBlockElementAdd']
);

$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    [Iblock::class, 'OnAfterIBlockElementUpdate']
);
?>