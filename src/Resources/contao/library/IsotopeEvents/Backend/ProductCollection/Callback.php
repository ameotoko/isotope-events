<?php

namespace IsotopeEvents\Backend\ProductCollection;


use Isotope\Backend\ProductCollection\Callback;
use Isotope\Isotope;
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
			$args[2]   = 'XXX '.$arrTokens['hcard_fn'];
		}

		$args[3] = Isotope::formatPriceWithCurrency($row['total']);

		/** @var \Isotope\Model\OrderStatus $objStatus */
		if (($objStatus = $objOrder->getRelated('order_status')) !== null) {
			$args[4] = '<span style="' . $objStatus->getColorStyles() . '">' . $objOrder->getStatusLabel() . '</span>';
		} else {
			$args[4] = '<span>' . $objOrder->getStatusLabel() . '</span>';
		}

		dump($args);
		return $args;
	}
}