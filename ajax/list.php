<?php

/**
 * This file contains package_quiqqer_areas_ajax_list
 */

/**
 * Returns area list for a grid
 *
 * @param string $params - JSON query params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_areas_ajax_list',
    function ($params) {
        $Areas  = new QUI\ERP\Areas\Handler();
        $result = array();
        $Locale = QUI::getLocale();

        $Grid = new \QUI\Utils\Grid();
        
        $data = $Areas->getChildrenData(
            $Grid->parseDBParams(json_decode($params, true))
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

        return $Grid->parseResult($result, $Areas->countChildren());
    },
    array('params'),
    'Permission::checkAdminUser'
);
