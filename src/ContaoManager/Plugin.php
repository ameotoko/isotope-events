<?php

declare(strict_types=1);

namespace Ameotoko\IsotopeEvents\ContaoManager;

use Ameotoko\IsotopeEvents\AmeotokoIsotopeEventsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(AmeotokoIsotopeEventsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, 'isotope'])
        ];
    }
}
