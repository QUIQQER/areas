<?php

/**
 * This file contains QUI\ERP\Areas\Import
 */

namespace QUI\ERP\Areas;

use QUI;
use QUI\Utils\DOM;
use QUI\Utils\Text\XML;

/**
 * Class Import
 * @package QUI\ERP\Areas
 */
class Import
{
    /**
     * @return array
     */
    public static function getAvailableImports()
    {
        $dir      = OPT_DIR . 'quiqqer/areas/setup/';
        $xmlFiles = QUI\Utils\System\File::readDir($dir);
        $result   = [];

        foreach ($xmlFiles as $xmlFile) {
            $Document = XML::getDomFromXml($dir . $xmlFile);
            $Path     = new \DOMXPath($Document);
            $title    = $Path->query("//quiqqer/title");

            if ($title->item(0)) {
                $result[] = [
                    'file'   => $xmlFile,
                    'locale' => DOM::getTextFromNode($title->item(0), false)
                ];
            }
        }

        return $result;
    }

    /**
     * Import areas from a preconfigure file
     *
     * @param string $fileName - file.xml
     * @throws QUI\Exception
     */
    public static function importPreconfigureAreas($fileName)
    {
        if (self::existPreconfigure($fileName) === false) {
            throw new QUI\Exception(
                ['quiqqer/areas', 'exception.preconfigure.file.not.found'],
                404
            );
        }

        self::import(OPT_DIR . 'quiqqer/areas/setup/' . $fileName);
    }

    /**
     * Exists the preconfigure file?
     *
     * @param string $file
     * @return boolean
     */
    public static function existPreconfigure($file)
    {
        $availableImports = QUI\ERP\Areas\Import::getAvailableImports();

        foreach ($availableImports as $data) {
            if ($file == $data['file']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Import the standard areas
     *
     * @param string $xmlFile - XML File, path to the xml file
     */
    public static function import($xmlFile)
    {
        $Document = XML::getDomFromXml($xmlFile);
        $Path     = new \DOMXPath($Document);

        $areas = $Path->query("//quiqqer/areas/area");
        $Areas = new QUI\ERP\Areas\Handler();

        foreach ($areas as $Area) {
            /* @var $Area \DOMElement */
            $countries = $Area->getElementsByTagName('countries');
            $title     = $Area->getElementsByTagName('title');

            if (!$title->item(0)) {
                continue;
            }

            /* @var $Title \DOMElement */
            $Title  = $title->item(0);
            $locale = $Title->getElementsByTagName('locale');

            $countryList = [];

            if ($countries->item(0)) {
                $countries = trim($countries->item(0)->nodeValue);
                $countries = explode(',', $countries);

                foreach ($countries as $country) {
                    if ($country === '{$currentCountry}') {
                        try {
                            $country = QUI\Countries\Manager::getDefaultCountry()->getCode();
                        } catch (QUI\Exception $Exception) {
                            continue;
                        }
                    }

                    try {
                        $Country       = QUI\Countries\Manager::get($country);
                        $countryList[] = $Country->getCode();
                    } catch (QUI\Exception $Exception) {
                    }
                }
            }

            if ($locale->item(0)) {
                $group = $locale->item(0)->getAttribute('group');
                $var   = $locale->item(0)->getAttribute('var');

                $localeValue = "[{$group}] {$var}";
            } else {
                $localeValue = trim($Title->nodeValue);
            }

            try {
                $Areas->createChild([
                    'countries' => implode(',', $countryList),
                    'data'      => json_encode(['importLocale' => $localeValue])
                ]);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        try {
            QUI\Translator::publish('quiqqer/areas');
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeRecursive($Exception);
        }
    }
}
