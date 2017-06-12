<?php

/**
 * This file contains QUI\ERP\Areas\Handler
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\Permissions\Permission;

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
            $newVar  = 'area.' . $NewArea->getId() . '.title';
            $current = QUI::getLocale()->getCurrent();

            $title = $NewArea->getAttribute('title');

            if (QUI::getLocale()->isLocaleString($title)) {
                $parts = QUI::getLocale()->getPartsOfLocaleString($title);
                $title = QUI::getLocale()->get($parts[0], $parts[1]);
            }

            try {
                QUI\Translator::addUserVar('quiqqer/areas', $newVar, array(
                    $current   => $title,
                    'datatype' => 'php,js',
                    'package'  => 'quiqqer/areas'
                ));
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
        return array(
            'title',
            'countries'
        );
    }

    /**
     *
     * @return array
     */
    public function getUnAssignedCountries()
    {
        $result   = array();
        $children = $this->getChildrenData();

        $signedCountries    = array();
        $availableCountries = QUI\Countries\Manager::getAllCountryCodes();

        foreach ($children as $entry) {
            $countries       = explode(',', trim($entry['countries']));
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
    public function search($freeText, $queryParams = array())
    {
        $areas  = $this->getChildren();
        $result = array();

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
                    if (mb_stripos($Country->getName(), $freeText) !== false
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
                $start   = $explode[0];
                $max     = $explode[1];
            } else {
                $max = (int)$queryParams['limit'];
            }

            $result = array_slice($result, $start, $max);
        }


        return $result;
    }
}
