<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;

/**
 * @Hook("generatePage")
 */
class GeneratePageListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        //
    }
}
