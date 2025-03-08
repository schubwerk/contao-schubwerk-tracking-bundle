<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\Services;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Config
{
    public function __construct(ContaoFramework $framework)
    {
        $framework->initialize();
        $this->container = System::getContainer();
    }

    public function getTrackerBaseUrl()
    {
        if (empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_base'])) {
            return 'https://tracker.schubwerk.de';
        }

        return $GLOBALS['TL_CONFIG']['schubwerk_tracking_base'];
    }

    public function getApiKey()
    {
        return $GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'];
    }

    public function getCacheDir()
    {
        $cacheDir = $this->container->getParameter('kernel.cache_dir') . '/schubwerk_tracking';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        return $cacheDir;
    }

    public function getWebDir()
    {
        $webDir = $this->container->getParameter('contao.web_dir');
        return $webDir;
    }

    public function getContaoVersion()
    {
        return ContaoCoreBundle::getVersion();
    }
}
