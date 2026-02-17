<?php

/**
 * This files contains QUI\ERP\Areas\Utils
 */

namespace QUI\ERP\Areas;

use Exception;
use QUI;
use QUI\Countries\Country;
use QUI\Interfaces\Users\User;

/**
 * Class Utils
 * Helper for area usage
 */
class Utils
{
    /**
     * Checks if the user in the area list
     *
     * @param User $User - user
     * @param array<int, Area|int|string> $areas - list of areas or area ids
     * @return bool
     */
    public static function isUserInAreas(User $User, array $areas = []): bool
    {
        $Areas = new Handler();
        $Country = $User->getCountry();

        foreach ($areas as $Area) {
            if (!($Area instanceof Area)) {
                try {
                    $Area = $Areas->getChild($Area);
                } catch (QUI\Exception) {
                    continue;
                }
            }

            if ($Country && $Area->contains($Country)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the address in the area list
     *
     * @param QUI\ERP\Address|QUI\Users\Address $Address
     * @param array<int, Area|int|string> $areas - list of areas or area ids
     * @return bool
     */
    public static function isAddressInArea(
        QUI\ERP\Address | QUI\Users\Address $Address,
        array $areas = []
    ): bool {
        $Areas = new Handler();

        try {
            $Country = $Address->getCountry();
        } catch (Exception) {
            return false;
        }

        foreach ($areas as $Area) {
            if (!($Area instanceof Area)) {
                try {
                    $Area = $Areas->getChild($Area);
                } catch (QUI\Exception) {
                    continue;
                }
            }

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
     * @return null|Area
     */
    public static function getAreaByCountry(mixed $Country): ?Area
    {
        if (!QUI\Countries\Manager::isCountry($Country)) {
            return null;
        }

        $Areas = new Handler();

        try {
            $areas = $Areas->getChildren();
        } catch (QUI\Exception) {
            return null;
        }

        foreach ($areas as $Area) {
            if ($Area->contains($Country)) {
                return $Area;
            }
        }

        return null;
    }
}
