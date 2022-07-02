<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Ameotoko\IsotopeEvents\Module;

use Haste\Http\Response\HtmlResponse;
use Haste\Input\Input;
use Isotope\Model\Product;
use Isotope\Module\ProductReader as ProductReaderBase;

/**
 * Class ProductReader
 */
class ProductReader extends ProductReaderBase
{
    /**
     * Generate module
     * @return void
     */
    protected function compile()
    {
        global $objPage;
        global $objIsotopeListPage;

        $objProduct = Product::findAvailableByIdOrAlias(Input::getAutoItem('product'));

        if (null === $objProduct) {
            $this->generate404();
        }

        $arrElements = array();
        $objCte = \ContentModel::findPublishedByPidAndTable($objProduct->getProductId(), 'tl_iso_product');

        if ($objCte !== null) {
            $intCount = 0;
            $intLast = $objCte->count() - 1;

            while ($objCte->next()) {
                $arrCss = array();

                /** @var ContentModel $objRow */
                $objRow = $objCte->current();

                // Add the "first" and "last" classes (see #2583)
                if ($intCount == 0 || $intCount == $intLast) {
                    if ($intCount == 0) {
                        $arrCss[] = 'first';
                    }

                    if ($intCount == $intLast) {
                        $arrCss[] = 'last';
                    }
                }

                $objRow->classes = $arrCss;
                $arrElements[] = $this->getContentElement($objRow, $this->strColumn);
                ++$intCount;
            }
        }

        $arrConfig = array(
            'module'      => $this,
            'template'    => $this->iso_reader_layout ? : $objProduct->getType()->reader_template,
            'gallery'     => $this->iso_gallery ? : $objProduct->getType()->reader_gallery,
            'buttons'     => $this->iso_buttons,
            'useQuantity' => $this->iso_use_quantity,
            'jumpTo'      => $objIsotopeListPage ? : $objPage,
            'elements'    => $arrElements
        );

        if (\Environment::get('isAjaxRequest')
            && \Input::post('AJAX_MODULE') == $this->id
            && \Input::post('AJAX_PRODUCT') == $objProduct->getProductId()
        ) {
            try {
                $objResponse = new HtmlResponse($objProduct->generate($arrConfig));
                $objResponse->send();
            } catch (\InvalidArgumentException $e) {
                return;
            }
        }

        $this->addMetaTags($objProduct);
        $this->addCanonicalProductUrls($objProduct);

        $this->Template->product       = $objProduct->generate($arrConfig);
        $this->Template->product_id    = $this->getCssId($objProduct);
        $this->Template->product_class = $this->getCssClass($objProduct);
        $this->Template->referer       = 'javascript:history.go(-1)';
        $this->Template->back          = $GLOBALS['TL_LANG']['MSC']['goBack'];
    }

    /**
     * Generates a 404 page and stops page output.
     */
    private function generate404()
    {
        global $objPage;
        /** @var \PageError404 $objHandler */
        $objHandler = new $GLOBALS['TL_PTY']['error_404']();
        $objHandler->generate($objPage->id);
        exit;
    }
}
