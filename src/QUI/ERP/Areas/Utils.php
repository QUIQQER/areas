<?php

/**
 * This files contains QUI\ERP\Areas\Utils
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\Countries\Country;
use QUI\Interfaces\Users\User;

/**
 * Class Utils
 * Helper for area usage
 *
 * @package QUI\ERP\Areas
 */
class Utils
{
    /**
     * Checks if the user in the area list
     *
     * @param User $User - user
     * @param array $areas - list of areas or areas ids
     * @return bool
     */
    public static function isUserInAreas(User $User, array $areas)
    {
        $Areas   = new Handler();
        $Country = $User->getCountry();

        foreach ($areas as $Area) {
            if (!is_object($Area) || get_class($Area) != Area::class) {
                try {
                    $Area = $Areas->getChild($Area);
                } catch (QUI\Exception $Exception) {
                    continue;
                }
            }

            /* @var $Area Area */
            if ($Area->contains($Country)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the area where the country is in
     *
     * @param Country $Country
     * @return bool|Area
     */
    public static function getAreaByCountry($Country)
    {
        if (!QUI\Countries\Manager::isCountry($Country)) {
            return false;
        }

        $Areas = new Handler();
        $areas = $Areas->getChildren();

        /* @var $Area Area */
        foreach ($areas as $Area) {
            if ($Area->contains($Country)) {
                return $Area;
            }
        }

        return false;
    }
}
