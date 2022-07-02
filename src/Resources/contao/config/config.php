<?php

use Ameotoko\IsotopeEvents\EventListener\DcaManager;
use Ameotoko\IsotopeEvents\EventListener\InsertTags;
use Ameotoko\IsotopeEvents\Model\Location;
use Ameotoko\IsotopeEvents\Module\ProductListCalendar;
use Ameotoko\IsotopeEvents\Module\ProductReader;

// Location feature
$GLOBALS['TL_MODELS'][Location::getTable()] = Location::class;
$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'][] = 'tl_iso_location';

$GLOBALS['ISO_MOD']['config']['location'] = array(
  'tables'            => array('tl_iso_location'),
  'icon'              => 'bundles/ameotokoisotopeevents/img/setup-locations.png',
);

// Mini calendar module
$GLOBALS['FE_MOD']['isotope']['iso_productlist'] = ProductListCalendar::class;

// Use content elements in products
$GLOBALS['BE_MOD']['isotope']['iso_products']['tables'][] = 'tl_content';
$GLOBALS['FE_MOD']['isotope']['iso_productreader'] = ProductReader::class;

// Add option to format price as integer
$GLOBALS['ISO_NUM']["10,000"]    = array(0, '.', ",");

// Inserttags
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [InsertTags::class, 'replaceInsertTags'];

// Clean up variants in case of cancelled copying of product
$GLOBALS['TL_HOOKS']['reviseTable'][] = [DcaManager::class, 'onReviseTable'];
