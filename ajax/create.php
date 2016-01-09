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
    function () {
        $Areas = new QUI\ERP\Areas\Handler();
        $Area  = $Areas->createChild();

        return $Area->getId();
    },
    false,
    'Permission::checkAdminUser'
);
