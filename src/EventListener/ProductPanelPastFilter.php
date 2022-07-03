<?php

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Input;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class ProductPanelPastFilter
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Callback(table="tl_iso_product", target="list.sorting.panel_callback.past")
     */
    public function add_filter(): string
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $filters = $sessionBag->get('filter');

        $strBuffer = '<div class="tl_filter tl_subpanel" style="margin: 6px 0;">';
        $strBuffer .= '<label for="pastFilter"><strong>Show past events: </strong></label>';
        $strBuffer .= sprintf('<input type="checkbox" id="pastFilter" name="pastFilter" value="1" onchange="this.form.submit();"%s>', $filters['pastFilter'] ? ' checked' : '');
        $strBuffer .= '</div>';

        return $strBuffer;
    }

    /**
     * @Callback(table="tl_iso_product", target="config.onload")
     */
    public function apply_filter(): void
    {
        if ($this->requestStack->getCurrentRequest()->query->has('id')) {
            return;
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $filters = $sessionBag->get('filter');

        if (Input::post('FORM_SUBMIT') == 'tl_filters') {
            $filters['pastFilter'] = Input::post('pastFilter') ? '1' : '0';

            $sessionBag->set('filter', $filters);
        }

        if ($filters['pastFilter'] == 0) {
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = ['begin>=?', time()];
        }
    }
}
