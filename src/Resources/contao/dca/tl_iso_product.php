<?php

use Ameotoko\IsotopeEvents\EventListener\DcaManager;
use Ameotoko\IsotopeEvents\EventListener\ProductPanelPastFilter;
use Ameotoko\IsotopeEvents\Model\Location;
use Contao\ArrayUtil;
use Contao\Input;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

// Event begin
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['begin'] = [
    'exclude' => false,
    'inputType' => 'text',
    'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'notnull' => false],
    'eval' => ['rgxp' => 'datim', 'tl_class' => 'w50 wizard'],
    'attributes' => ['legend' => 'general_legend']
];

// Event end
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['end'] = [
    'exclude' => false,
    'inputType' => 'text',
    'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'notnull' => false],
    'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
    'attributes' => ['legend' => 'general_legend']
];

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['addtime'] = [
    'exclude' => false,
    'inputType' => 'checkbox',
    'sql' => ['type' => Types::STRING, 'length' => 1, 'fixed' => true, 'default' => ''],
    'eval' => ['tl_class' => 'w50'],
    'attributes' => ['legend' => 'general_legend']
];

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['available'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['availability'],
    'exclude' => false,
    'inputType' => 'select',
    'options' => ['available', 'unavailable', 'full'],
    'reference' => &$GLOBALS['TL_LANG']['tl_iso_product'],
    'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
    'eval' => ['tl_class' => 'w50'],
    'attributes' => ['legend' => 'publish_legend']
);

// Location feature
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => Location::getTable() . '.name',
    'options_callback' => [Location::class, 'getLocations'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
    'attributes' => ['legend' => 'general_legend'],
];

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location_override'] = array(
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => ['type' => Types::STRING, 'length' => 1, 'fixed' => true, 'default' => ''],
    'attributes' => ['legend' => 'general_legend']
);

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location_note'] = array(
    'filter' => false,
    'search' => true,
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
    'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
    'attributes' => ['legend' => 'general_legend']
);

// Concert datetime attribute
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['begin']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = ['begin'];

// Custom filter: Show/Hide past events
if (!Input::get('id')) {
    $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['panelLayout'] = str_replace(';filter;', ';filter,past;', $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['panelLayout']);
    $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['panel_callback']['past'] = [ProductPanelPastFilter::class, 'add_filter'];

    $GLOBALS['TL_DCA']['tl_iso_product']['config']['onload_callback']['past'] = [ProductPanelPastFilter::class, 'apply_filter'];
}

// Use content elements in products
if (Input::get('do') == 'iso_products' && (!Input::get('id') || Input::get('act') == 'copy' || Input::get('act') == 'delete')) {
    $GLOBALS['TL_DCA']['tl_iso_product']['config']['ctable'][] = 'tl_content';
    $GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['edit']['href'] = 'table=tl_content';
    ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations'], 1, [
        'editheader' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['edit'],
            'href' => 'act=edit',
            'icon' => 'header.svg'
        ]
    ]);
}

// Publish variants by default when copying a product
$GLOBALS['TL_DCA']['tl_iso_product']['config']['oncopy_callback'][] = [DcaManager::class, 'publishNewVariant'];
