<?php 
namespace Dev\Site\Helpers;


class IBlock {
const LOG_IBLOCK_CODE = 'LOG';



    /**
     * Получает или создает инфоблок LOG
     */

public static function getOrCreateLogIblock()
    {
        // Ищем существующий
        $dbIblock = \CIBlock::GetList([], [
            'CODE' => self::LOG_IBLOCK_CODE,
            'CHECK_PERMISSIONS' => 'N'
        ]);
        
        if ($iblock = $dbIblock->Fetch()) {
            return (int)$iblock['ID'];
        }
        

        self::getOrCreateIBlockType();

        $iblockType = "LOG_TYPE";

        $siteId = 's1';

        // Создаем новый инфоблок
        $iblock = new \CIBlock;
        
        $arFields = [
            'ACTIVE' => 'Y',
            'NAME' => 'Лог изменений элементов',
            'CODE' => self::LOG_IBLOCK_CODE,
            'IBLOCK_TYPE_ID' => $iblockType,
            'SITE_ID' => $siteId,
            'GROUP_ID' => ['2' => 'R'],
            'VERSION' => 1,
        ];
        
        $iblockId = $iblock->Add($arFields);
        
        if (!$iblockId) {
            return false;
        }
        
        // Добавляем свойство "Тип действия"
        $property = new \CIBlockProperty;
        $property->Add([
            'NAME' => 'Тип действия',
            'ACTIVE' => 'Y',
            'CODE' => 'ACTION_TYPE',
            'IBLOCK_ID' => $iblockId,
            'PROPERTY_TYPE' => 'S',
            'IS_REQUIRED' => 'N',
        ]);
        
        
        return $iblockId;
    }

    /**
     * Создает нужный тип инфоблока для логов, если его еще нет
     */
    public static function getOrCreateIBlockType()
    {
        if (!$dbType = \CIBlockType::GetList(
            Array(),
            Array("ID" => "LOG_TYPE")
        )->Fetch()) {
            $iblockType = new \CIBlockType;
    
            // Массив параметров нового типа
            $arFields = Array(
                'ID' => 'LOG_TYPE', // Символьный код типа (обязательно)
                'SECTIONS' => 'Y',        // Использовать разделы
                'IN_RSS' => 'N',          // Показывать в RSS
                'SORT' => 100,            // Сортировка
                'LANG' => Array(
                    'ru' => Array(
                        'NAME' => 'Логи', // Название на русском
                        'SECTION_NAME' => 'Разделы',
                        'ELEMENT_NAME' => 'Элементы'
                    )
                )
            );

            // Добавляем тип
            $res = $iblockType->Add($arFields);   
        }
    }




        /**
     * Получает информацию об инфоблоке
     */
    public static function getIblockInfo($iblockId)
    {
        $iblock = \CIBlock::GetByID($iblockId)->Fetch();
        return $iblock ?: false;
    }
    
    /**
     * Получает полный путь к разделу (рекурсивно)
     */
    public static function getElementSectionPath($elementId)
    {
        $dbSections = \CIBlockElement::GetElementGroups($elementId, true);
        $sectionIds = [];
        while ($section = $dbSections->Fetch()) {
            $sectionIds[] = (int)$section['ID'];
        }
        
        if (empty($sectionIds)) {
            return 'Корень';
        }
        
        $sectionId = $sectionIds[0];
        

        $path = getSectionPathRecursive($sectionId);
        
        return $path ?: 'Корень';
    }
    
    /**
     * Рекурсивный сбор пути раздела
     */
    public static function getSectionPathRecursive($sectionId, $depth = 0)
    {
        if ($depth > 50) return '';
        
        $section = \CIBlockSection::GetByID($sectionId)->Fetch();
        if (!$section) {
            return '';
        }
        
        $sectionName = $section['NAME'];
        
        if ($section['IBLOCK_SECTION_ID'] > 0) {
            $parentPath = getSectionPathRecursive($section['IBLOCK_SECTION_ID'], $depth + 1);
            return $parentPath . ' -> ' . $sectionName;
        }
        
        return $sectionName;
    }
    
    /**
     * Получает имя элемента
     */
    public static function getElementName($elementId)
    {
        $element = \CIBlockElement::GetByID($elementId)->Fetch();
        return $element ? $element['NAME'] : "Элемент {$elementId}";
    }
    
    /**
     * Определяет дату для активности
     */
    public static function getActivityDate($elementId, $action)
    {
        $element = \CIBlockElement::GetByID($elementId)->Fetch();
        
        if ($action == 'add') {
            $date = $element['DATE_CREATE'];
        } else {
            $date = $element['TIMESTAMP_X'];
        }
        
        return $date ?: date('Y-m-d H:i:s');
    }
    
    /**
     * Получает или создает раздел в лог-инфоблоке
     */
    public static function getOrCreateLogSection($logIblockId, $iblockName, $iblockCode)
    {
        // Ищем раздел по коду
        $dbSection = \CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $logIblockId,
                'CODE' => $iblockCode,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            ['ID']
        );
        
        if ($section = $dbSection->Fetch()) {
            return (int)$section['ID'];
        }
        
        // Создаем новый раздел
        $bs = new \CIBlockSection;
        $arFields = [
            'IBLOCK_ID' => $logIblockId,
            'NAME' => $iblockName,
            'CODE' => $iblockCode,
            'ACTIVE' => 'Y',
        ];
        
        $sectionId = $bs->Add($arFields);
        
        return $sectionId ?: false;
    }
    
    /**
     * Ищет существующий элемент лога
     */
    public static function findExistingLogElement($logIblockId, $sectionId, $logElementName)
    {
        $dbElement = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $logIblockId,
                'SECTION_ID' => $sectionId,
                'NAME' => $logElementName,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            false,
            ['ID']
        );
        
        if ($element = $dbElement->Fetch()) {
            return (int)$element['ID'];
        }
        
        return false;
    }
    
    /**
     * Создает новый элемент в лог-инфоблоке
     */
    public static function createLogElement($logIblockId, $sectionId, $name, $description, $activityDate, $action)
    {
        $el = new \CIBlockElement;
        
        $arFields = [
            'IBLOCK_ID' => $logIblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => $name,
            'PREVIEW_TEXT' => $description,
            'ACTIVE_FROM' => $activityDate,
            'ACTIVE' => 'Y',
        ];
        
        $elId = $el->Add($arFields);
        
        if ($elId) {
            \CIBlockElement::SetPropertyValues($elId, $logIblockId, ($action == 'add' ? 'Создание' : 'Изменение'), 'ACTION_TYPE');
        }
    }
    
    /**
     * Обновляет существующий элемент лога
     */
    public static function updateLogElement($logElementId, $description, $activityDate)
    {
        $el = new \CIBlockElement;
        
        $arFields = [
            'PREVIEW_TEXT' => $description,
            'ACTIVE_FROM' => $activityDate,
        ];
        
        $el->Update($logElementId, $arFields);
    }

}

?>