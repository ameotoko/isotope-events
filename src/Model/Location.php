<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\Model;

use Contao\CoreBundle\Intl\Countries;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Isotope\Translation;

/**
 * @property int    $id
 * @property string $name
 * @property string $street
 * @property string $postal
 * @property string $city
 * @property string $state
 * @property $country
 * @property $note
 */
class Location extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_location';

    /**
     * Return the localized name
     */
    public function getName(): string
    {
        return Translation::get($this->name);
    }

    /**
     * Return the localized order status name.
     * Do not use $this->getName(), the alias should not be localized.
     */
    public function getAlias(): string
    {
        return StringUtil::standardize($this->name);
    }

    public function getLocalizedCountry()
    {
        $countries = System::getContainer()->get(Countries::class)->getCountries();

        return $countries[$this->country];
    }

    /**
     * Get locations and return them as array
     */
    public static function getLocations(): array
    {
        $arrLocations = array();

        if (($objLocations = Location::findAll()) !== null) {

            /** @var Location $location */
            foreach ($objLocations as $location) {
                $arrLocations[$location->id] = $location->getName();
            }
        }

        return $arrLocations;
    }

    public static function getLocation($id = ''): ?static
    {
        return static::findByPk($id);
    }
}
