<?php

$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['label_callback'] = ['IsotopeEvents\Backend\ProductCollection\CustomCallback', 'getOrderLabel'];

// We need to use on of existing fields and replace it with our contents, so we don't alter the table
$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['fields'][] = 'config_id';

if ('BE' == TL_MODE && \Input::get('do') == 'iso_orders') {
	$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['billing_address_id']['label'] = 'Name';
	$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['config_id']['label'] = 'Events';
}

// Events filter
$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['panelLayout'] .= ';events_filter';

$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['panel_callback']['events_filter'] = ['IsotopeEvents\Backend\ProductCollection\CustomCallback', 'generateEventsFilter'];

$GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['onload_callback'][] = ['IsotopeEvents\Backend\ProductCollection\CustomCallback', 'applyEventsFilter'];

// Don't need this filter
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['shipping_id']['filter'] = false;

