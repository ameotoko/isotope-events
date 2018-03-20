<?php

$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['label_callback'] = ['IsotopeEvents\Backend\ProductCollection\CustomCallback', 'getOrderLabel'];

$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['fields'][] = 'config_id';

if ('BE' == TL_MODE && \Input::get('do') == 'iso_orders') {
	$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['billing_address_id']['label'] = 'Name';
	$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['config_id']['label'] = 'Events';
}