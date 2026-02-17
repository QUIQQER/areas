<?php

/**
 * This file contains QUI\ERP\Areas\Handler
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\CRUD\Child;
use QUI\Database\Exception;
use QUI\Permissions\Permission;

use function is_string;
use function json_decode;

/**
 * Class Handler
 * Create and handles areas
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
    public function getDataBaseTableName(): string
    {
        return QUI::getDBTableName('areas');
    }

    /**
     * Return the name of the child crud class
     *
     * @return string
     */
    public function getChildClass(): string
    {
        return 'QUI\ERP\Areas\Area';
    }

    /**
     * Return the crud attributes for the children class
     *
     * @return list<string>
     */
    public function getChildAttributes(): array
    {
        return [
            'countries',
            'data'
        ];
    }

    public function getChild(int | string $id): Area
    {
        /* @var $child Area */
        $child = parent::getChild($id);

        // @phpstan-ignore-next-line
        return $child;
    }

    /**
     * Return the list of all unassigned countries
     *
     * @return list<string>
     * @throws Exception
     */
    public function getUnAssignedCountries(): array
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
     * @param array{limit?: string|int} $queryParams
     * @return list<Area>
     * @throws Exception
     */
    public function search(string $freeText, array $queryParams = []): array
    {
        $areas = $this->getChildren();
        $result = [];

        if (empty($freeText)) {
            foreach ($areas as $Area) {
                $result[] = $Area;
            }
        } else {
            foreach ($areas as $Area) {
                if (mb_stripos($Area->getTitle(), $freeText) !== false) {
                    $result[] = $Area;
                    continue;
                }

                $countries = $Area->getCountries();

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
            $limit = (string)$queryParams['limit'];

            if (str_contains($limit, ',')) {
                $explode = explode(',', $limit);
                $start = (int)$explode[0];
                $max = (int)$explode[1];
            } else {
                $max = (int)$limit;
            }

            $result = array_slice($result, $start, $max);
        }


        return $result;
    }

    /**
     * Return the translation vars for an area
     *
     * @param Area $NewArea
     * @return array<string, string>
     */
    public function getLocaleData(QUI\ERP\Areas\Area $NewArea): array
    {
        $availableLanguages = QUI\Translator::getAvailableLanguages();

        if (!is_array($availableLanguages)) {
            $availableLanguages = [];
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
