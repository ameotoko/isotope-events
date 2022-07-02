<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Date;
use Contao\StringUtil;
use Haste\Util\Format;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;
use Isotope\Model\TaxClass;
use Symfony\Component\Filesystem\Path;

/**
 * Generate product label in backend
 *
 * @Callback(table="tl_iso_product", target="list.label.label")
 */
class ProductLabelCallback
{
    private string $projectDir;
    private ImageFactoryInterface $imageFactory;

    public function __construct(string $projectDir, ImageFactoryInterface $imageFactory)
    {
        $this->projectDir = $projectDir;
        $this->imageFactory = $imageFactory;
    }

    public function __invoke(array $row, string $label, DataContainer $dc, array $args): array
    {
        $objProduct = Product::findByPk($row['id']);

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
            switch ($field) {
                case 'images':
                    $args[$i] = static::generateImage($objProduct);
                    break;

                case 'name':
                    $args[$i] = $this->generateName($row, $objProduct, $dc);
                    break;

                case 'price':
                    $args[$i] = $this->generatePrice($row);
                    break;

                case 'variantFields':
                    $args[$i] = $this->generateVariantFields($args[$i], $objProduct, $dc);
                    break;
            }
        }

        return $args;
    }

    private function generateImage(Product $objProduct): string
    {
        $arrImages = StringUtil::deserialize($objProduct->images);

        if (!empty($arrImages) && is_array($arrImages)) {
            foreach ($arrImages as $image) {
                $strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];
                $srcPath = Path::join($this->projectDir, $strImage);

                if (is_file($srcPath)) {
                    $size = @getimagesize($srcPath);

                    $script = sprintf(
                        "Backend.openModalImage({'width':%s,'title':'%s','url':'%s'});return false",
                        $size[0],
                        str_replace("'", "\\'", $objProduct->name),
                        TL_FILES_URL . $strImage
                    );

                    /** @noinspection BadExpressionStatementJS */
                    /** @noinspection HtmlUnknownTarget */
                    return sprintf(
                        '<a href="%s" onclick="%s"><img src="%s" alt="%s" align="left"></a>',
                        TL_FILES_URL . $strImage,
                        $script,
                        $this->imageFactory->create($srcPath, [50, 50, 'proportional'])->getUrl($this->projectDir),
                        $image['alt']
                    );
                }
            }
        }

        return '&nbsp;';
    }

    private function generateName(array $row, Product $objProduct, DataContainer $dc): string
    {
        $date = sprintf(
            '<span style="color: #999;">%s</span><br>',
            Date::parse('d F Y, H:i', $objProduct->begin)
        );

        // Add a variants link
        if ($row['pid'] == 0
            && ($objProductType = ProductType::findByPk($row['type'])) !== null
            && $objProductType->hasVariants()
        ) {
            /** @noinspection HtmlUnknownTarget */
            return sprintf(
                $date.'<a href="%s" title="%s">%s</a>',
                StringUtil::ampersand(\Environment::get('request')) . '&amp;id=' . $row['id'],
                StringUtil::specialchars($GLOBALS['TL_LANG'][$dc->table]['showVariants']),
                $objProduct->name
            );
        }

        return $date.$objProduct->name;
    }

    private function generatePrice(array $row): string
    {
        $objPrice = ProductPrice::findPrimaryByProductId($row['id']);

        if (null !== $objPrice) {
            try {
                /** @var TaxClass $objTax */
                $objTax = $objPrice->getRelated('tax_class');
                $strTax = (null === $objTax ? '' : ' (' . $objTax->getName() . ')');

                return $objPrice->getValueForTier(1) . $strTax;
            } catch (\Exception $e) {
                return '';
            }
        }

        return '';
    }

    private function generateVariantFields(string $label, Product $objProduct, DataContainer $dc): string
    {
        $attributes = [];

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
            $attributes[] = sprintf(
                '<strong>%s:</strong>&nbsp;%s',
                Format::dcaLabel($dc->table, $variantField),
                Format::dcaValue($dc->table, $variantField, $objProduct->$variantField)
            );
        }

        return ($label ? $label . '<br>' : '') . implode(', ', $attributes);
    }
}
