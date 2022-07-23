<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

use Ameotoko\IsotopeEvents\Model\Location;
use Ameotoko\IsotopeEvents\Module\ProductListCalendar;
use Ameotoko\IsotopeEvents\Module\ProductReader;

// Location feature
$GLOBALS['TL_MODELS'][Location::getTable()] = Location::class;
$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'][] = 'tl_iso_location';

$GLOBALS['ISO_MOD']['config']['location'] = [
    'tables' => ['tl_iso_location'],
    'icon' => 'bundles/ameotokoisotopeevents/img/setup-locations.png',
];

// Mini calendar module
$GLOBALS['FE_MOD']['isotope']['iso_productlist'] = ProductListCalendar::class;

// Use content elements in products
$GLOBALS['BE_MOD']['isotope']['iso_products']['tables'][] = 'tl_content';
$GLOBALS['FE_MOD']['isotope']['iso_productreader'] = ProductReader::class;

// Add option to format price as integer
$GLOBALS['ISO_NUM']["10,000"] = [0, '.', ","];
