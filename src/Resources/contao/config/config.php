<?php

// Location feature
$GLOBALS['TL_MODELS'][\Isotope\Model\Location::getTable()] = 'Isotope\Model\Location';
$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'][] = 'tl_iso_location';

$GLOBALS['ISO_MOD']['config']['location'] = array(
  'tables'            => array('tl_iso_location'),
  'icon'              => 'bundles/ameotokoisotopeevents/img/setup-locations.png',
);

// Mini calendar module
$GLOBALS['FE_MOD']['isotope']['iso_productlist'] = 'IsotopeEvents\Module\ProductListCalendar';

// Use content elements in products
$GLOBALS['BE_MOD']['isotope']['iso_products']['tables'][] = 'tl_content';
$GLOBALS['FE_MOD']['isotope']['iso_productreader'] = 'Isotope\Module\ProductReaderCustom';

// Add option to format price as integer
$GLOBALS['ISO_NUM']["10,000"]    = array(0, '.', ",");

// Inserttags
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['IsotopeEvents\Helper', 'replaceInsertTags'];

// Clean up variants in case of cancelled copying of product
$GLOBALS['TL_HOOKS']['reviseTable'][] = ['Isotope\Backend\Product\DcaManagerCustom', 'onReviseTable'];
