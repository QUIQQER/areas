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

QUI::getAjax()->registerFunction(
    'package_quiqqer_areas_ajax_search',
    function ($freeText, $params) {
        $Areas = new QUI\ERP\Areas\Handler();
        $result = [];
        $Locale = QUI::getLocale();
        $areas = $Areas->search($freeText, json_decode($params, true));

        foreach ($areas as $Area) {
            $result[] = [
                'id' => $Area->getId(),
                'countries' => $Area->getAttribute('countries'),
                'title' => $Area->getTitle($Locale),
                'text' => $Area->getTitle($Locale)
            ];
        }

        usort($result, function ($a, $b) {
            return strcmp($a['text'], $b['text']);
        });

        return $result;
    },
    ['freeText', 'params'],
    'Permission::checkAdminUser'
);
