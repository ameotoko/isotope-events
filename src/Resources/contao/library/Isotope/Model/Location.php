<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Isotope\Translation;


/**
 * @property int    $id
 * @property string $name
 * @property string $street
 * @property string $postal
 * @property string $city
 * @property string $state
 * @property $country
 */
class Location extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_location';

    /**
     * Return the localized order status name
     * @return  string
     */
    public function getName()
    {
        return Translation::get($this->name);
    }

    /**
     * Return the localized order status name
     * Do not use $this->getName(), the alias should not be localized
     * @return  string
     */
    public function getAlias()
    {
        return standardize($this->name);
    }

    /**
     * Get locations and return them as array
     *
     * @return array
     */
    public static function getLocations()
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

    public static function getLocation($id='')
    {
        return static::findByPk($id);
    }
}
