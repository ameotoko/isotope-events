<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

use Contao\Input;
use Isotope\Model\Address;

// We need to use on of existing fields and replace it with our contents, so we don't alter the table
$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['fields'] = ['document_number', 'locked', 'billing_address_id', 'total', 'order_status', 'config_id'];

if ('BE' == TL_MODE && Input::get('do') == 'iso_orders') {
    $GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['billing_address_id']['label'] = ['Name'];
    $GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['config_id']['label'] = 'Events';
}

// Events filter
$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['panelLayout'] .= ';events_filter';

// Don't need this filter
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['shipping_id']['filter'] = false;

// Search orders in backend by the client's last name
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['billing_address_id']['search'] = true;
$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['billing_address_id']['foreignKey'] = Address::getTable().'.lastname';
