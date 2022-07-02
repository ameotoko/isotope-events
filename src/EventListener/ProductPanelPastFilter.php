<?php

namespace Ameotoko\IsotopeEvents\EventListener;

class ProductPanelPastFilter
{
    public function add_filter()
    {
        $session = \Session::getInstance()->getData();

        $strBuffer = '<div class="tl_filter tl_subpanel" style="margin: 6px 0;">';
        $strBuffer .= '<strong>Show past events: </strong>';
        $strBuffer .= '<input type="checkbox" name="pastFilter" value="1" onchange="this.form.submit();"'.($session['filter']['pastFilter'] ? ' checked' : '').'>';
        $strBuffer .= '</div>';

        return $strBuffer;
    }

    public function apply_filter()
    {
        $session = \Session::getInstance()->getData();

        if (\Input::post('FORM_SUBMIT') == 'tl_filters') {
            $session['filter']['pastFilter'] = \Input::post('pastFilter') ? '1' : '0';
            \Session::getInstance()->setData($session);
        }

        if ($session['filter']['pastFilter'] == 0) {
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = ['begin>=?', time()];
        }
    }
}
