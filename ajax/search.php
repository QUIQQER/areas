<?php

/**
 * This file contains package_quiqqer_areas_ajax_search
 */

/**
 * Returns area list
 *
 * @param string $freeText - Freetext search, String to search
 * @param string $params - JSON query params
 *
 * @return array
 */

use QUI\ERP\Areas\Area;

QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_search',
    function ($freeText, $params) {
        $Areas = new QUI\ERP\Areas\Handler();
        $result = [];
        $Locale = QUI::getLocale();

        $areas = $Areas->search($freeText, json_decode($params, true));

        /* @var $Area Area */
        foreach ($areas as $Area) {
            $result[] = [
                'id' => $Area->getId(),
                'countries' => $Area->getAttribute('countries'),
                'title' => $Area->getTitle($Locale),
                'text' => $Area->getTitle($Locale)
            ];
        }

        usort($result, function ($a, $b) {
            return $a['text'] > $b['text'];
        });

        return $result;
    },
    ['freeText', 'params'],
    'Permission::checkAdminUser'
);
