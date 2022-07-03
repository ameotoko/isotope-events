<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;

/**
 * @Callback(table="tl_iso_product_collection", target="list.label.label")
 */
class OrderLabelCallback
{
    public function __invoke($row, $label, DataContainer $dc, $args): array
    {
        /** @var Order $objOrder */
        $objOrder = Order::findByPk($row['id']);

        if (null === $objOrder) {
            return $args;
        }

        /** @var Config $config */
        $config = $objOrder->getRelated('config_id');

        // Override system to correctly format currencies etc
        Isotope::setConfig($config);

        $objAddress = $objOrder->getBillingAddress();

        if (null !== $objAddress) {
            $arrTokens = $objAddress->getTokens(Isotope::getConfig()->getBillingFieldsConfig());
            $args[2]   = $arrTokens['hcard_fn'];
        }

        $args[3] = Isotope::formatPriceWithCurrency($row['total']);

        /** @var OrderStatus $objStatus */
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
}
