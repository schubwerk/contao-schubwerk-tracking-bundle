<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\CoreBundle\ContaoCoreBundle;
use Schubwerk\ContaoSchubwerkTrackingBundle\ContaoSchubwerkTrackingBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoSchubwerkTrackingBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
