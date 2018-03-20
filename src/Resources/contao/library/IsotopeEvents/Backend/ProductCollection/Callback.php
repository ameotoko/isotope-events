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
			$args[2]   = $arrTokens['hcard_fn'];
		}

		$arrOrderItems = $objOrder->getItems();
		$firstItem = array_shift($arrOrderItems);
		$strNames = $firstItem->getName();
		foreach ($arrOrderItems as $objOrderItem) {
			$strNames .= ', '.$objOrderItem->getName();
		}

		$args[2] .= sprintf(' <span style="color: #c4c4c4;">(%s)</span>', $strNames);
		
		$args[3] = Isotope::formatPriceWithCurrency($row['total']);

		/** @var \Isotope\Model\OrderStatus $objStatus */
		if (($objStatus = $objOrder->getRelated('order_status')) !== null) {
			$args[4] = '<span style="' . $objStatus->getColorStyles() . '">' . $objOrder->getStatusLabel() . '</span>';
		} else {
			$args[4] = '<span>' . $objOrder->getStatusLabel() . '</span>';
		}

		return $args;
	}
}