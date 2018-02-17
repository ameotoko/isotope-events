<?php

// Location feature
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location'] = array
(
  'label'                 => ['Location'],
  'exclude'               => true,
  'inputType'             => 'select',
  'foreignKey'            => \Isotope\Model\Location::getTable().'.name',
  'options_callback'      => array('\Isotope\Model\Location', 'getLocations'),
  'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
  'sql'                   => "int(10) NOT NULL default '0'",
  'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
  'attributes'            => array('legend'=>'general_legend'),
);

// Concert datetime attribute
$GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['label_callback'] = array('Isotope\Backend\Product\CustomLabel', 'generate');
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['begin']['sorting'] = true;

