<?php

/**
 * This file contains QUI\ERP\Areas\Area
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\Permissions\Permission;

/**
 * Class Handler
 *
 * @package QUI\ERP\Areas
 */
class Area extends QUI\CRUD\Child
{
    /**
     * @var array|null
     */
    protected ?array $countries = null;

    /**
     * Area constructor.
     *
     * @param int $id
     * @param QUI\CRUD\Factory $Factory
     */
    public function __construct($id, QUI\CRUD\Factory $Factory)
    {
        parent::__construct($id, $Factory);

        $this->Events->addEvent('onDeleteBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.delete');
        });

        $this->Events->addEvent('onDeleteEnd', function () {
            QUI\Translator::delete(
                'quiqqer/areas',
                'area.' . $this->getId() . '.title'
            );
        });

        $this->Events->addEvent('onSaveBegin', function () {
            Permission::checkPermission('quiqqer.areas.area.edit');
        });
    }

    /**
     * @param null|QUI\Locale $Locale - optional
     * @return array|string
     */
    public function getTitle(QUI\Locale $Locale = null): array|string
    {
        if ($Locale === null) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get(
            'quiqqer/areas',
            'area.' . $this->getId() . '.title'
        );
    }

    /**
     * Return the country list of the area
     *
     * @return array|null - [QUI\Countries\Country, QUI\Countries\Country, QUI\Countries\Country]
     */
    public function getCountries(): ?array
    {
        if (!is_null($this->countries)) {
            return $this->countries;
        }

        $result = [];
        $countries = $this->getAttribute('countries');
        $countries = explode(',', $countries);

        foreach ($countries as $country) {
            try {
                $Country = QUI\Countries\Manager::get($country);
                $result[] = $Country;
            } catch (QUI\Exception $Exception) {
            }
        }

        $this->countries = $result;

        return $result;
    }

    /**
     * Is the country in the area?
     *
     * @param string|QUI\Countries\Country $Country
     * @return boolean
     */
    public function contains(QUI\Countries\Country|string $Country): bool
    {
        if (!($Country instanceof QUI\Countries\Country)) {
            try {
                $Country = QUI\Countries\Manager::get($Country);
            } catch (QUI\Exception $Exception) {
                return false;
            }
        }

        $countries = $this->getCountries();

        /* @var $Entry QUI\Countries\Country */
        foreach ($countries as $Entry) {
            if ($Entry->getCode() == $Country->getCode()) {
                return true;
            }
        }

        return false;
    }
}
