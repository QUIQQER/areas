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
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_getUnAssignedCountries',
    function () {
        $Handler = new \QUI\ERP\Areas\Handler();

        return $Handler->getUnAssignedCountries();
    },
    [],
    'Permission::checkAdminUser'
);
