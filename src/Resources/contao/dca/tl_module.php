<?php

// Mini calendar module
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist'] = str_replace('{template_legend', '{calendar_legend:hide},asCalendar;{template_legend', $GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']);

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'asCalendar';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['asCalendar'] = 'cal_startDay';

$GLOBALS['TL_DCA']['tl_module']['fields']['asCalendar'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['asCalendar'],
	'inputType' => 'checkbox',
	'exclude' => true,
	'eval' => array('submitOnChange' => true),
	'sql' => "char(1) NOT NULL default ''",
);

