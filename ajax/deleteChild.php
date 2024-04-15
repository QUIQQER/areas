<?php

/**
 * This file contains package_quiqqer_areas_ajax_delete
 */

/**
 * Delete an area
 *
 * @param string|int $areaId - Area-ID
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_deleteChild',
    function ($areaId) {
        $Areas = new QUI\ERP\Areas\Handler();
        $Area = $Areas->getChild($areaId);
        $Area->delete();
    },
    ['areaId'],
    'Permission::checkAdminUser'
);
