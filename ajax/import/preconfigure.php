<?php

/**
 * This file contains package_quiqqer_areas_ajax_import_preconfigure
 */

/**
 * Returns the available imports
 *
 * @param string $importName
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_import_preconfigure',
    function ($importName) {
        QUI\ERP\Areas\Import::importPreconfigureAreas($importName);

        try {
            QUI\Translator::publish('quiqqer/areas');
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeRecursive($Exception);
        }
    },
    ['importName'],
    'Permission::checkAdminUser'
);
