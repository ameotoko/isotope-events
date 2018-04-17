<?php

namespace IsotopeEvents\Backend\ProductCollection;


use Contao\Model\Collection;
use Isotope\Backend\ProductCollection\Callback;
use Isotope\Isotope;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;

class CustomCallback extends Callback
{

	/**
	 * Generate the order label and return it as string
	 *
	 * @param array          $row
	 * @param string         $label
	 * @param \DataContainer $dc
	 * @param array          $args
	 *
	 * @return string
	 */
	public function getOrderLabel($row, $label, \DataContainer $dc, $args)
	{
		/** @var Order $objOrder */
		$objOrder = Order::findByPk($row['id']);

		if (null === $objOrder) {
			return $args;
		}

		// Override system to correctly format currencies etc
		Isotope::setConfig($objOrder->getRelated('config_id'));

		$objAddress = $objOrder->getBillingAddress();

		if (null !== $objAddress) {
			$arrTokens = $objAddress->getTokens(Isotope::getConfig()->getBillingFieldsConfig());
			$args[2]   = $arrTokens['hcard_fn'];
		}

		$args[3] = Isotope::formatPriceWithCurrency($row['total']);

		/** @var \Isotope\Model\OrderStatus $objStatus */
		if (($objStatus = $objOrder->getRelated('order_status')) !== null) {
			$args[4] = '<span style="' . $objStatus->getColorStyles() . '">' . $objOrder->getStatusLabel() . '</span>';
		} else {
			$args[4] = '<span>' . $objOrder->getStatusLabel() . '</span>';
		}

		$strNames = array();

		foreach ($objOrder->getItems() as $objOrderItem) {
			$strNames[] = $objOrderItem->getName();
		}

		$args[5] = sprintf('<span style="color: #a6a6a6;">%s</span>', implode(', ', $strNames));

		return $args;
	}

	public function generateEventsFilter()
	{
		/** @var Collection $orders */
		$orders = Order::findBy(
			['type = ?', 'order_status != ?', 'locked != ?'],
			['order', '0', '']
		);

		$arrOptions = [];

		/** @var Order $objOrder */
		foreach ($orders as $objOrder) {

			foreach ($objOrder->getItems() as $objOrderItem) {

				$objProduct = $objOrderItem->getProduct();

				// Only unique products in array
				$arrOptions[$objProduct->getProductId()] = [
					'id' => $objProduct->getProductId(),
					'name' => $objProduct->getName()
				];
			}
		}

		$session = \Session::getInstance()->getData();

		$strHtml = '<div class="tl_subpanel" style="width: 60%">
		<strong>Filter events:</strong>
		<select name="eventsFilter" id="eventsFilter" class="tl_select" style="width: 70%; margin-left: 3px;" onchange="this.form.submit();">
		  <option value="eventsFilter">Event</option>
		  <option value="eventsFilter">---</option>';

		foreach ($arrOptions as $option) {
			$strHtml .= sprintf('<option value="%s"%s>%s</option>',
				$option['id'],
				($session['filter']['eventsFilter'] == $option['id']) ? ' selected' : '',
				$option['name']);
		}

		$strHtml .= '</select></div>';

		return $strHtml;
	}

	public function applyEventsFilter()
	{
		$session = \Session::getInstance()->getData();

		if (\Input::post('FORM_SUBMIT') == 'tl_filters') {

			$session['filter']['eventsFilter'] = \Input::post('eventsFilter');
			\Session::getInstance()->setData($session);
		}

		if (\Input::post('filter_reset')) {

			$session['filter']['eventsFilter'] = 'eventsFilter';
			\Session::getInstance()->setData($session);
		}

		if ($session['filter']['eventsFilter'] != 'eventsFilter') {

			/** @var Product $objProduct */
			if ($objProduct = Product::findByPk($session['filter']['eventsFilter'])) {
				$arrVariants = $objProduct->getVariantIds ();

				$arrVariants[] = $objProduct->getId ();

				$res = \Database::getInstance ()
					->query ('SELECT id FROM tl_iso_product_collection WHERE id IN (SELECT pid FROM tl_iso_product_collection_item WHERE product_id IN (' . implode (',', $arrVariants) . '))');

				$GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['filter'][] =
					['id IN (' . implode (',', $res->fetchEach ('id')) . ')', ''];
			}
		}

	}

}