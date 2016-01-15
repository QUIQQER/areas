<?php

/**
 * This file contains package_quiqqer_areas_ajax_search
 */

/**
 * Returns area list
 *
 * @param string $params - JSON query params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_search',
    function ($params) {
        $Areas  = new QUI\ERP\Areas\Handler();
        $result = array();
        $Locale = QUI::getLocale();

        $data = $Areas->getChildrenData(
            json_decode($params, true)
        );

        foreach ($data as $entry) {
            $result[] = array(
                'id' => $entry['id'],
                'countries' => $entry['countries'],
                'title' => $Locale->getPartsOfLocaleString($entry['title']),
                'text' => $Locale->parseLocaleString($entry['title'])
            );
        }

        usort($result, function ($a, $b) {
            return $a['text'] > $b['text'];
        });

        return $result;
    },
    array('params'),
    'Permission::checkAdminUser'
);
