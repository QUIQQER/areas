<?php

/**
 * This file contains QUI\ERP\Areas\Handler
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\Permissions\Permission;

use function is_string;
use function json_decode;

/**
 * Class Handler
 * Create and handles areas
 *
 * @package QUI\ERP\Areas
 */
class Handler extends QUI\CRUD\Factory
{
    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->Events->addEvent('onCreateBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.create');
        });

        // create new translation var for the area
        $this->Events->addEvent('onCreateEnd', function ($NewArea) {
            /* @var $NewArea QUI\ERP\Areas\Area */
            $newVar = 'area.' . $NewArea->getId() . '.title';
            $locale = $this->getLocaleData($NewArea);

            $locale['datatype'] = 'php,js';
            $locale['package'] = 'quiqqer/areas';

            try {
                QUI\Translator::addUserVar('quiqqer/areas', $newVar, $locale);
            } catch (QUI\Exception $Exception) {
                QUI::getMessagesHandler()->addAttention(
                    $Exception->getMessage()
                );
            }
        });
    }

    /**
     * return the area db table name
     *
     * @return string
     */
    public function getDataBaseTableName()
    {
        return QUI::getDBTableName('areas');
    }

    /**
     * Return the name of the child crud class
     *
     * @return string
     */
    public function getChildClass()
    {
        return 'QUI\ERP\Areas\Area';
    }

    /**
     * Return the crud attributes for the children class
     *
     * @return array
     */
    public function getChildAttributes()
    {
        return [
            'countries',
            'data'
        ];
    }

    /**
     * Return the list of all unassigned countries
     *
     * @return array
     */
    public function getUnAssignedCountries()
    {
        $result = [];
        $children = $this->getChildrenData();

        $signedCountries = [];
        $availableCountries = QUI\Countries\Manager::getAllCountryCodes();

        foreach ($children as $entry) {
            $countries = explode(',', trim($entry['countries']));
            $signedCountries = array_merge($signedCountries, $countries);
        }

        $signedCountries = array_flip($signedCountries);

        foreach ($availableCountries as $code) {
            if (!isset($signedCountries[$code])) {
                $result[] = $code;
            }
        }

        return $result;
    }

    /**
     * Search some areas
     *
     * @param string $freeText
     * @param array $queryParams
     * @return array
     */
    public function search($freeText, $queryParams = [])
    {
        $areas = $this->getChildren();
        $result = [];

        if (empty($freeText)) {
            $result = $areas;
        } else {
            /* @var $Area Area */
            foreach ($areas as $Area) {
                if (mb_stripos($Area->getTitle(), $freeText) !== false) {
                    $result[] = $Area;
                    continue;
                }

                $countries = $Area->getCountries();

                /* @var $Country QUI\Countries\Country */
                foreach ($countries as $Country) {
                    if (
                        mb_stripos($Country->getName(), $freeText) !== false
                        || mb_stripos($Country->getCode(), $freeText) !== false
                        || mb_stripos($Country->getCodeToLower(), $freeText) !== false
                        || mb_stripos($Country->getCurrencyCode(), $freeText) !== false
                    ) {
                        $result[] = $Area;
                        continue 2;
                    }
                }
            }
        }

        if (isset($queryParams['limit'])) {
            $start = 0;

            if (strpos($queryParams['limit'], ',') !== false) {
                $explode = explode(',', $queryParams['limit']);
                $start = $explode[0];
                $max = $explode[1];
            } else {
                $max = (int)$queryParams['limit'];
            }

            $result = array_slice($result, $start, $max);
        }


        return $result;
    }

    /**
     * Return the translation vars for an area
     *
     * @param Area $NewArea
     * @return array
     */
    public function getLocaleData(QUI\ERP\Areas\Area $NewArea)
    {
        try {
            $availableLanguages = QUI\Translator::getAvailableLanguages();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return [];
        }

        $result = [];
        $title = '';
        $data = $NewArea->getAttribute('data');

        if (empty($data)) {
            return $result;
        }

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (isset($data['importLocale'])) {
            $title = $data['importLocale'];
        }

        if (empty($title) && $NewArea->getAttribute('title')) {
            $title = $NewArea->getAttribute('title');
        }

        foreach ($availableLanguages as $language) {
            $Locale = new QUI\Locale();
            $Locale->setCurrent($language);

            $currentTitle = $NewArea->getTitle($Locale);

            if (!empty($currentTitle) && !QUI::getLocale()->isLocaleString($currentTitle)) {
                continue;
            }

            $parts = QUI::getLocale()->getPartsOfLocaleString($title);

            if (count($parts) === 2) {
                $result[$language] = QUI::getLocale()->getByLang($language, $parts[0], $parts[1]);

                continue;
            }

            // getPartsOfLocaleString returns an array
            $result[$language] = $title;
        }

        return $result;
    }
}
