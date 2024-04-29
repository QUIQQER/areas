<?php

/**
 * This file contains package_quiqqer_areas_ajax_getUnAssignedCountries
 */

/**
 * Returns the available imports
 *
 * @param string $id - Area-ID
 *
 * @return array
 */

use QUI\ERP\Areas\Handler;

QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_getUnAssignedCountries',
    function () {
        $Handler = new Handler();

        return $Handler->getUnAssignedCountries();
    },
    [],
    'Permission::checkAdminUser'
);
