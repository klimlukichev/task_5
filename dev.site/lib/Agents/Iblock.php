<?php

namespace Dev\Site\Agents;

\CModule::IncludeModule('iblock');
class Iblock
{
    public static function clearOldLogs() {
        
        $logIblock = \CIBlock::GetList(
                [],
                [
                    'CODE' => 'LOG',
                    'CHECK_PERMISSIONS' => 'N'
                ],
                false
            )->Fetch();
            
        if (!$logIblock) {
            return __FUNCTION__.'();';
        }
            
        $iblockId = $logIblock['ID'];
        
        $newestIds = [];
        $rsNewest = \CIBlockElement::GetList(
            ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            ['nTopCount' => 10],
            ['ID']
        );

        while ($arNewest = $rsNewest->Fetch()) {
            $newestIds[] = (int)$arNewest['ID'];
        }

        $rsLogs = \CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CHECK_PERMISSIONS' => 'N',
                '!ID' => $newestIds
            ],
            false,
            false,
            ['ID']
        );
        
        $deletedCount = 0;
        $deletedIds = [];
        
        while ($arLog = $rsLogs->Fetch()) {
            $result = \CIBlockElement::Delete($arLog['ID']);
            if ($result) {
                $deletedCount++;
                $deletedIds[] = $arLog['ID'];
            }
        }
        return __FUNCTION__.'();';
}

    
}
?>