<?php

/**
 * This file contains package_quiqqer_areas_ajax_get
 */

/**
 * Returns an area
 *
 * @param string $id - Area-ID
 *
 * @return array
 */
QUI::getAjax()->registerFunction(
    'package_quiqqer_areas_ajax_get',
    function ($id) {
        $Areas = new QUI\ERP\Areas\Handler();
        $Area = $Areas->getChild($id);
        $attributes = $Area->getAttributes();

        $attributes['title'] = $Area->getTitle();

        return $attributes;
    },
    ['id'],
    'Permission::checkAdminUser'
);
