<?php

/**
 * This file contains QUI\ERP\Areas\Setup
 */

namespace QUI\ERP\Areas;

use QUI;

/**
 * Class Setup
 * @package QUI\ERP\Areas
 */
class Setup
{
    /**
     * @param QUI\Package\Package $Package
     */
    public static function onPackageSetup(QUI\Package\Package $Package)
    {
        // cleanup table
        $table = QUI::getDBTableName('areas');

        if (QUI::getDataBase()->table()->existColumnInTable($table, 'title')) {
            QUI::getDataBase()->table()->deleteColumn($table, 'title');
        }


        // import locale for areas
        $Areas    = new QUI\ERP\Areas\Handler();
        $children = $Areas->getChildrenData();
        $group    = 'quiqqer/areas';

        try {
            $availableLanguages = QUI\Translator::getAvailableLanguages();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return;
        }

        foreach ($children as $child) {
            try {
                /* @var $Area Area */
                $Area = $Areas->getChild($child['id']);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
                continue;
            }

            $var = 'area.'.$Area->getId().'.title';
            $new = [];

            $localeData = QUI\Translator::get($group, $var);
            $areaLocale = $Areas->getLocaleData($Area);

            foreach ($availableLanguages as $language) {
                if (isset($localeData[$language]) && !empty($localeData[$language])) {
                    continue;
                }

                if (isset($areaLocale[$language])) {
                    $new[$language] = $areaLocale[$language];
                }
            }

            if (empty($new)) {
                continue;
            }

            try {
                QUI\Translator::update($group, $var, $group, $new);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        try {
            QUI\Translator::publish($group);
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeRecursive($Exception);
        }
    }

    /**
     * Import the standard areas
     *
     * @param string $xmlFile
     */
    public static function import($xmlFile)
    {
        QUI\ERP\Areas\Import::import($xmlFile);
    }
}
