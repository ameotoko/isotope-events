<?php

namespace Isotope\Backend\Product;

class CustomPanel extends \Backend
{
	public function add_filter()
	{ 
		$session = \Session::getInstance()->getData();

		$strBuffer = '<div class="tl_filter tl_subpanel">';
		$strBuffer .= '<strong>Show past events: </strong>';
		$strBuffer .= '<select name="pastFilter" id="pastFilter" class="tl_select'.(($session['filter']['pastFilter']) ? ' active' : '').'" onchange="this.form.submit();">';
		$strBuffer .=	'<option value="0">Show</option><option value="1"'.(($session['filter']['pastFilter']) ? ' selected' : '').'>Hide</option>';
		$strBuffer .= '</select>';
		$strBuffer .= '</div>';

		return $strBuffer;
	}

	public function apply_filter()
	{
		$session = \Session::getInstance()->getData();

		if (\Input::post('FORM_SUBMIT') == 'tl_filters') {
				$session['filter']['pastFilter'] = \Input::post('pastFilter');
				\Session::getInstance()->setData($session);
		}

		if ($session['filter']['pastFilter'] == 1) {
			$GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = ['begin>=?', time()];
		}
	}
}
