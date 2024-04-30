<?php

/**
 * This file contains package_quiqqer_areas_ajax_import_upload
 */

/**
 * Returns the available imports
 *
 * @param QDOM $id - Area-ID
 *
 * @return array
 */

use QUI\QDOM;

QUI::getAjax()->registerFunction(
    'package_quiqqer_areas_ajax_import_upload',
    function ($File) {
        /* @var $File QDOM */
        QUI\ERP\Areas\Import::import(
            $File->getAttribute('filepath')
        );
    },
    ['File'],
    'Permission::checkAdminUser'
);
