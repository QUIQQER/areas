<?php

/**
 * This file contains package_quiqqer_areas_ajax_import_available
 */

/**
 * Returns the available imports
 *
 * @param string $id - Area-ID
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_import_available',
    function () {
        return \QUI\ERP\Areas\Import::getAvailableImports();
    },
    array(),
    'Permission::checkAdminUser'
);
