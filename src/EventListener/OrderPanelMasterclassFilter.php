<?php

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Date;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class OrderPanelMasterclassFilter
{
    private RequestStack $requestStack;
    private Connection $connection;

    public function __construct(RequestStack $requestStack, Connection $connection)
    {
        $this->requestStack = $requestStack;
        $this->connection = $connection;
    }

    /**
     * @Callback(table="tl_iso_product_collection", target="list.sorting.panel_callback.events_filter")
     */
    public function addFilter(): string
    {
        // Get product id (distinct), name and start date from each existing order
        $query = $this->connection->createQueryBuilder()
            ->from('tl_iso_product_collection_item', 'item')
            ->leftJoin('item', 'tl_iso_product', 'p', 'item.product_id = p.id')
            ->leftJoin('item', 'tl_iso_product_collection', 'pc', 'item.pid = pc.id')
            ->select('p.id', 'p.name', 'p.begin')
            ->where('pc.type = ?')
            ->andWhere('pc.order_status != ?')
            ->andWhere('pc.locked IS NOT NULL')
            ->groupBy('p.id')
            ->getSQL()
        ;

        $result = $this->connection->fetchAllAssociative($query, ['order', 0]);

        $options = [];

        foreach ($result as $option) {
            $options[$option['id']] = [
                'id' => $option['id'],
                'name' => sprintf(
                    '[%s] %s',
                    $option['begin'] === null ? 'ONLINE' : Date::parse('F Y', $option['begin']),
                    $option['name']
                )
            ];
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $filters = $sessionBag->get('filter');

        $strHtml = '<div class="tl_subpanel" style="width: 60%">
		<strong>' . $GLOBALS['TL_LANG']['tl_iso_product_collection']['filterEvents'] . ':</strong>
		<select name="eventsFilter" id="eventsFilter" class="tl_select tl_chosen" style="width: 70%; margin-left: 3px;" onchange="this.form.submit();">
		  <option value="eventsFilter">---</option>';

        foreach ($options as $option) {
            $strHtml .= sprintf(
                '<option value="%s"%s>%s</option>',
                $option['id'],
                ($filters['eventsFilter'] == $option['id']) ? ' selected' : '',
                $option['name']
            );
        }

        $strHtml .= '</select></div>';

        return $strHtml;
    }

    /**
     * @Callback(table="tl_iso_product_collection", target="config.onload")
     */
    public function applyEventsFilter()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $filters = $sessionBag->get('filter');

        if (Input::post('FORM_SUBMIT') == 'tl_filters') {
            $filters['eventsFilter'] = Input::post('eventsFilter');
            $sessionBag->set('filter', $filters);
        }

        if (Input::post('filter_reset')) {
            $filters['eventsFilter'] = 'eventsFilter';
            $sessionBag->set('filter', $filters);
        }

        if ($filters['eventsFilter'] != 'eventsFilter') {

            /** @var Product $objProduct */
            if ($objProduct = Product::findByPk($filters['eventsFilter'])) {
                $arrVariants = $objProduct->getVariantIds();

                $arrVariants[] = $objProduct->getId();

                $ids = $this->connection
                    ->fetchFirstColumn('SELECT id FROM tl_iso_product_collection WHERE id IN (SELECT pid FROM tl_iso_product_collection_item WHERE product_id IN (' . implode(',', $arrVariants) . '))');

                if (count($ids)) {
                    $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['filter'][] = ['id IN (' . implode(',', $ids) . ')', ''];
                }
            }
        }
    }
}
