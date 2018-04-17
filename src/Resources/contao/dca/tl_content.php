<?php

// Use content elements in products
if (\Input::get('do') == 'iso_products' && \Input::get('table') == 'tl_content') {
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_iso_product';
}
