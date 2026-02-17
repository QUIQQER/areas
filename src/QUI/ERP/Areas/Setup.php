<?php

/**
 * This file contains QUI\ERP\Areas\Setup
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\Database\Exception;

/**
 * Class Setup
 */
class Setup
{
    /**
     * @param QUI\Package\Package $Package
     * @throws Exception
     * @codeCoverageIgnore
     */
    public static function onPackageSetup(QUI\Package\Package $Package): void
    {
        // cleanup table
        $table = QUI::getDBTableName('areas');

        if (QUI::getDataBase()->table()?->existColumnInTable($table, 'title')) {
            QUI::getDataBase()->table()->deleteColumn($table, 'title');
        }


        // import locale for areas
        $Areas = new QUI\ERP\Areas\Handler();
        $children = $Areas->getChildrenData();
        $group = 'quiqqer/areas';

        $availableLanguages = QUI\Translator::getAvailableLanguages();

        if (!is_array($availableLanguages)) {
            $availableLanguages = [];
        }

        foreach ($children as $child) {
            try {
                $Area = $Areas->getChild($child['id']);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
                continue;
            }

            $var = 'area.' . $Area->getId() . '.title';
            $new = [];

            $localeData = QUI\Translator::get($group, $var);
            $areaLocale = $Areas->getLocaleData($Area);

            foreach ($availableLanguages as $language) {
                if (!empty($localeData[$language])) {
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
    public static function import(string $xmlFile): void
    {
        QUI\ERP\Areas\Import::import($xmlFile);
    }
}
