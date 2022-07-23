<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

use Contao\CoreBundle\Intl\Countries;
use Isotope\Backend;

$GLOBALS['TL_DCA']['tl_iso_location'] = [
    'config' => [
        'dataContainer' => 'Table',
        'label' => &$GLOBALS['TL_LANG']['IMD']['location'][0],
        'enableVersioning' => true,
        'closed' => true,
        'onload_callback' => [
            [Backend::class, 'initializeSetupModule'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ]
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['name'],
            'headerFields' => ['name'],
            'disableGrouping' => true,
            'panelLayout' => 'filter;search,limit',
            // 'paste_button_callback' => array('Isotope\Backend\OrderStatus\Callback', 'pasteButton'),
            'icon' => 'system/modules/isotope/assets/images/traffic-light.png',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'back' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href' => 'mod=&table=',
                'class' => 'header_back',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'new' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_location']['new'],
                'href' => 'act=create&amp;mode=create',
                'class' => 'header_new',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_location']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_location']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif'
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_location']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_location']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false; Backend.getScrollOffset();"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_location']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ],
        ]
    ],

    'palettes' => [
        'default' => '{name_legend},name;{address_legend},street,postal,city,state,country,note;{publish_legend},published',
    ],

    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'postal' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'city' => [
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'state' => [
            'exclude' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'country' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'options_callback' => function () {
                return System::getContainer()->get(Countries::class)->getCountries();
            },
            'sql' => "varchar(2) NOT NULL default ''"
        ],
        'published' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''"
        ],
        'note' => [
            'exclude' => true,
            'filter' => false,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => "text NULL"
        ],
    ]
];
