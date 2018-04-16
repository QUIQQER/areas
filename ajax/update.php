<?php

/**
 * This file contains package_quiqqer_areas_ajax_update
 */

/**
 * Returns area list
 *
 * @param string|int $areaId - Area-ID
 * @param string $params - JSON Area attributes
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_update',
    function ($areaId, $params) {
        $Areas  = new QUI\ERP\Areas\Handler();
        $Area   = $Areas->getChild($areaId);
        $params = json_decode($params, true);

        $Area->setAttributes($params);
        $Area->update();
    },
    ['areaId', 'params'],
    'Permission::checkAdminUser'
);
