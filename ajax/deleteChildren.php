<?php

/**
 * This file contains package_quiqqer_areas_ajax_deleteChildren
 */

/**
 * Delete multiple areas
 *
 * @param string $areaIds - JSON array of Area-IDs
 *
 * @return array
 */
QUI::getAjax()->registerFunction(
    'package_quiqqer_areas_ajax_deleteChildren',
    function ($areaIds) {
        $areaIds = json_decode($areaIds, true);
        $Areas = new QUI\ERP\Areas\Handler();
        $ExceptionStack = new QUI\ExceptionStack();

        foreach ($areaIds as $areaId) {
            try {
                $Area = $Areas->getChild($areaId);
                $Area->delete();
            } catch (QUI\Exception $Exception) {
                $ExceptionStack->addException($Exception);
            }
        }

        if (!$ExceptionStack->isEmpty()) {
            throw new $ExceptionStack();
        }
    },
    ['areaIds'],
    'Permission::checkAdminUser'
);
