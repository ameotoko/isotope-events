<?php

use Ameotoko\IsotopeEvents\EventListener\DcaManager;
use Ameotoko\IsotopeEvents\EventListener\ProductPanelPastFilter;
use Ameotoko\IsotopeEvents\Model\Location;
use Contao\Config;
use Contao\Date;
use Contao\Image;
use Contao\StringUtil;

// Additional fields
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['begin'] = array(
    'label'							=> &$GLOBALS['TL_LANG']['tl_iso_product']['begin'],
    'exclude'						=> false,
    'inputType'					=> 'text',
    'sql'								=> 'int(10) unsigned',
    'eval'							=> array('rgxp'=>'datim', 'tl_class'=>'w50 wizard'),
    'wizard' => [['tl_iso_product', 'datePickerRange']],
    'attributes'				=> array('legend' => 'general_legend')
);

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['end'] = array(
    'label'							=> &$GLOBALS['TL_LANG']['tl_iso_product']['end'],
    'exclude'						=> false,
    'inputType'					=> 'text',
    'sql'								=> 'int(10) unsigned',
    'eval'							=> array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
    'attributes'				=> array('legend' => 'general_legend')
);

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['addtime'] = array(
    'label'							=> &$GLOBALS['TL_LANG']['tl_iso_product']['addtime'],
    'exclude'						=> false,
    'inputType'					=> 'checkbox',
    'sql'								=> "char(1) not null default ''",
    'eval'							=> array('tl_class'=>'w50'),
    'attributes'				=> array('legend' => 'general_legend')
);

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['available'] = array(
    'label'							=> &$GLOBALS['TL_LANG']['tl_iso_product']['availability'],
    'exclude'						=> false,
    'inputType'					=> 'select',
    'options' => ['available', 'unavailable', 'full'],
    'reference' => &$GLOBALS['TL_LANG']['tl_iso_product'],
    'sql'								=> "char(16) not null default ''",
    'eval'							=> array('tl_class'=>'w50'),
    'attributes'				=> array('legend' => 'publish_legend')
);

// Location feature
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location'] = array(
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

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location_override'] = array(
  'label' => ['Override location note', 'If selected, the text below will be used'],
  'exclude' => true,
  'inputType' => 'checkbox',
  'eval' => ['submitOnChange'=>true],
  'sql' => 'char(1) NOT NULL default ""',
  'attributes' => array('legend'=>'general_legend')

);

$GLOBALS['TL_DCA']['tl_iso_product']['fields']['location_note'] = array(
    'label' => ['Note'],
    'filter' => false,
    'search' => true,
    'inputType' => 'textarea',
    'eval' => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
    'sql' => "text NULL",
    'attributes' => array('legend'=>'general_legend')
);

// Concert datetime attribute
$GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['label_callback'] = array('Isotope\Backend\Product\CustomLabel', 'generate');
$GLOBALS['TL_DCA']['tl_iso_product']['fields']['begin']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['fields'] = ['begin'];

// Custom filter: Show/Hide past events
if (!\Input::get('id')) {
    $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['panelLayout'] = str_replace(';filter;', ';filter,past;', $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['panelLayout']);
    $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['panel_callback']['past'] = [ProductPanelPastFilter::class, 'add_filter'];

    $GLOBALS['TL_DCA']['tl_iso_product']['config']['onload_callback']['past'] = [ProductPanelPastFilter::class, 'apply_filter'];
}

// Use content elements in products

if (\Input::get('do') == 'iso_products' && (!\Input::get('id') || \Input::get('act') == 'copy' || \Input::get('act') == 'delete')) {
    $GLOBALS['TL_DCA']['tl_iso_product']['config']['ctable'][] = 'tl_content';
    $GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['edit']['href'] = 'table=tl_content';
    array_insert($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations'], 1, [
        'editheader' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product']['edit'],
            'href'  => 'act=edit',
            'icon'  => 'header.svg'
        ]
    ]);
}

// Publish variants by default when copying a product
$GLOBALS['TL_DCA']['tl_iso_product']['config']['oncopy_callback'][] = [DcaManager::class, 'publishNewVariant'];

class tl_iso_product extends Backend {

    public function datePickerRange(DataContainer $dc) {
        $arrData = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field];
        $rgxp = $arrData['eval']['rgxp'];
        $format = Date::formatToJs(Config::get($rgxp . 'Format'));

        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/datepicker-range/js/datepicker-range.js';
        $GLOBALS['TL_CSS'][] = 'assets/datepicker-range/css/datepicker-range.css';

        switch ($rgxp)
        {
            case 'datim':
                $time = ",\n        timePicker: true";
                break;

            case 'time':
                $time = ",\n        pickOnly: \"time\"";
                break;

            default:
                $time = '';
                break;
        }

        $strOnSelect = '';

        // Trigger the auto-submit function (see #8603)
        if ($arrData['eval']['submitOnChange'])
        {
            $strOnSelect = ",\n        onSelect: function() { Backend.autoSubmit(\"" . $dc->table . "\"); }";
        }

        return ' ' . Image::getHtml('assets/datepicker/images/icon.svg', '', 'title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['datepicker']) . '" id="toggle_' . $dc->field . '" style="cursor:pointer"') . '
  <script>
    window.addEvent("domready", function() {
      new Picker.Date.Range($("ctrl_' . $dc->field . '"), {
        endDateField: $("ctrl_end"),
        draggable: false,
        toggle: $("toggle_' . $dc->field . '"),
        format: "' . $format . '",
        positionOffset: {x:-211,y:-209}' . $time . ',
        pickerClass: "datepicker_bootstrap",
        columns: 1,
        getStartEndDate: function(input) {
            return [input.get("value"), this.options.endDateField.get("value")].map(function(date){
            var parsed = Date.parse(date);
            return Date.isValid(parsed) ? parsed : null;
          }).clean();
        },
        setStartEndDate: function(input, dates){
          input.set("value", dates[0].format(this.options.format));
          this.options.endDateField.set("value", dates[1].format(this.options.format));
        },
        useFadeInOut: !Browser.ie' . $strOnSelect . ',
        startDay: ' . $GLOBALS['TL_LANG']['MSC']['weekOffset'] . ',
        titleFormat: "' . $GLOBALS['TL_LANG']['MSC']['titleFormat'] . '"
      });
    });
  </script>';
    }
}
