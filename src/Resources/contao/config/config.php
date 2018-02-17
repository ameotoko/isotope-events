<?php

// Location feature
$GLOBALS['TL_MODELS'][\Isotope\Model\Location::getTable()] = 'Isotope\Model\Location';
$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'][] = 'tl_iso_location';

$GLOBALS['ISO_MOD']['config']['location'] = array
(
  'tables'            => array('tl_iso_location'),
  'icon'              => 'bundles/ameotokoisotopeevents/img/setup-locations.png',
);