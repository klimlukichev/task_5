<?php

namespace Dev\Site\Handlers;

// require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/dev.site/lib/Helpers/IBlock.php';
class Iblock
{


    /**
     * Обработчик добавления элемента
     */
    public static function OnAfterIBlockElementAdd(&$arFields)
    {
        return self::addLog($arFields, 'add');
    }
    
    /**
     * Обработчик обновления элемента
     */
    public static function OnAfterIBlockElementUpdate(&$arFields)
    {
        return  self::addLog($arFields, 'update');
    }

    /**
     * Основная логика логирования
     */
    private static function addLog($arFields, $action)
    {
        if (!isset($arFields['IBLOCK_ID']) || !isset($arFields['ID'])) {
            return true;
        }
        
        $iblockId = (int)$arFields['IBLOCK_ID'];
        $elementId = (int)$arFields['ID'];
        
        $logIblockId =  \Dev\Site\Helpers\IBlock::getOrCreateLogIblock();
        
        if ($logIblockId === false) {
            return true;
        }
        
        // Исключаем логирование самого инфоблока LOG
        if ($iblockId == $logIblockId) {
            return true;
        }
        
        // Получаем информацию об инфоблоке
        $iblockInfo =  \Dev\Site\Helpers\IBlock::getIblockInfo($iblockId);
        if (!$iblockInfo) {
            return true;
        }
        
        // Получаем путь к разделу
        $sectionPath =  \Dev\Site\Helpers\IBlock::getElementSectionPath($elementId);
        
        // Получаем имя элемента
        $elementName =  \Dev\Site\Helpers\IBlock::getElementName($elementId);
        

        $description = $iblockInfo['NAME'] . ' -> ' . $sectionPath . ' -> ' . $elementName;
        
        
        $logElementName = (string)$elementId;
        
        // Дата активности
        $activityDate =  \Dev\Site\Helpers\IBlock::getActivityDate($elementId, $action);
        
    
        $sectionId = \Dev\Site\Helpers\IBlock::getOrCreateLogSection(
            $logIblockId,
            $iblockId,
            $iblockInfo['NAME'],
            $elementId
        );
        
        if ($sectionId === false) {
            return true;
        }
        
        // Ищем существующий элемент лога
        $existingLogId =  \Dev\Site\Helpers\IBlock::findExistingLogElement($logIblockId, $sectionId, $logElementName);
        
        if ($existingLogId) {
             \Dev\Site\Helpers\IBlock::updateLogElement($existingLogId, $description, $activityDate);
        } else {
            \Dev\Site\Helpers\IBlock::createLogElement($logIblockId, $sectionId, $logElementName, $description, $activityDate, $action);
        }
        
        return true;
    }
}
?>