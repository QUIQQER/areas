<?php

/**
 * This file contains package_quiqqer_areas_ajax_import_upload
 */

/**
 * Returns the available imports
 *
 * @param string $id - Area-ID
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_import_upload',
    function ($File) {
        \QUI\ERP\Areas\Import::import(
            $File->getAttribute('filepath')
        );
    },
    array('File'),
    'Permission::checkAdminUser'
);
