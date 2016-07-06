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
                    'datatype' => 'php,js'
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
     * Return an area
     *
     * @param int $id - Area-ID
     * @return QUI\ERP\Areas\Area
     * @throws QUI\Exception
     */
    public function getChild($id)
    {
        return parent::getChild($id);
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
}
