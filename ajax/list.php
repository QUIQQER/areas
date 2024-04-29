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
        $Areas = new QUI\ERP\Areas\Handler();
        $result = [];
        $Locale = QUI::getLocale();

        $Grid = new QUI\Utils\Grid();
        $data = $Areas->getChildrenData(
            $Grid->parseDBParams(json_decode($params, true))
        );

        foreach ($data as $entry) {
            try {
                /* @var $Area QUI\ERP\Areas\Area */
                $Area = $Areas->getChild($entry['id']);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
                continue;
            }

            $result[] = [
                'id' => $Area->getId(),
                'countries' => $Area->getAttribute('countries'),
                'title' => $Area->getTitle($Locale)
            ];
        }

        usort($result, function ($a, $b) {
            return strnatcmp($a['title'], $b['title']);
        });

        return $Grid->parseResult($result, $Areas->countChildren());
    },
    ['params'],
    'Permission::checkAdminUser'
);
