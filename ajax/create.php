<?php

/**
 * This file contains package_quiqqer_areas_ajax_create
 */

/**
 * Create an area
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_create',
    function ($params) {
        $params = json_decode($params, true);
        $Areas  = new QUI\ERP\Areas\Handler();
        $Area   = $Areas->createChild($params);

        try {
            QUI\Translator::publish('quiqqer/areas');
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeRecursive($Exception);
        }

        return $Area->getId();
    },
    ['params'],
    'Permission::checkAdminUser'
);
