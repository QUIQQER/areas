<?php

/**
 * This file contains QUI\ERP\Areas\Setup
 */

namespace QUI\ERP\Areas;

use QUI;

/**
 * Class Setup
 * @package QUI\ERP\Areas
 */
class Setup
{
    /**
     * Import the standard areas
     */
    public static function import($xmlFile)
    {
        QUI\ERP\Areas\Import::import($xmlFile);
    }
}
