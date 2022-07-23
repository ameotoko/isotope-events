<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Image;
use Contao\StringUtil;

/**
 * @Callback(table="tl_iso_product", target="fields.begin.wizard")
 */
class DatePickerRangeWizard
{
    public function __invoke(DataContainer $dc): string
    {
        $arrData = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field];
        $rgxp = $arrData['eval']['rgxp'];
        $format = Date::formatToJs(Config::get($rgxp . 'Format'));

        $GLOBALS['TL_JAVASCRIPT'][] = 'assets/datepicker-range/js/datepicker-range.js';
        $GLOBALS['TL_CSS'][] = 'assets/datepicker-range/css/datepicker-range.css';

        switch ($rgxp) {
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
        if ($arrData['eval']['submitOnChange'] ?? null) {
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
