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

use Contao\ContentModel;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Environment;
use Haste\Input\Input;
use Isotope\Model\Product;
use Isotope\Model\Product\AbstractProduct;
use Isotope\Module\ProductReader as ProductReaderBase;
use Symfony\Component\HttpFoundation\Response;

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
        $jumpTo = $GLOBALS['objIsotopeListPage'] ?: $GLOBALS['objPage'];

        if ($jumpTo->iso_readerMode === 'none') {
            throw new PageNotFoundException();
        }

        /** @var AbstractProduct $objProduct */
        $objProduct = Product::findAvailableByIdOrAlias(Input::getAutoItem('product'));

        if (null === $objProduct) {
            throw new PageNotFoundException();
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
            'disableOptions' => $this->iso_disable_options,
            'jumpTo'      => $jumpTo,
            'elements'    => $arrElements,
        );

        if (Environment::get('isAjaxRequest')
            && Input::post('AJAX_MODULE') == $this->id
            && Input::post('AJAX_PRODUCT') == $objProduct->getProductId()
            && !$this->iso_disable_options
        ) {
            try {
                $output = $objProduct->generate($arrConfig);
            } catch (\InvalidArgumentException $e) {
                return;
            }

            throw new ResponseException(new Response($output));
        }

        $this->addMetaTags($objProduct);
        $this->addCanonicalProductUrls($objProduct);

        $this->Template->product       = $objProduct->generate($arrConfig);
        $this->Template->product_id    = $objProduct->getCssId();
        $this->Template->product_class = $objProduct->getCssClass();
        $this->Template->referer       = 'javascript:history.go(-1)';
        $this->Template->back          = $GLOBALS['TL_LANG']['MSC']['goBack'];
    }
}
