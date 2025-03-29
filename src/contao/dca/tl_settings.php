<?php

$GLOBALS['TL_DCA']['tl_settings']['fields']['schubwerk_tracking_project_id'] =
    array(
        'inputType' => 'text',
        'eval' => array('mandatory' => false, 'tl_class' => 'clr'),
        'save_callback' => array(
            static function ($apiKey) {
                $container = \Contao\System::getContainer();
                $config = $container->get(\Schubwerk\ContaoSchubwerkTrackingBundle\Services\Config::class);
                $e = $config->ensureSclientDownloaded(true, $_POST['schubwerk_tracking_project_id'], $_POST['schubwerk_tracking_base']);
                if ($e !== null) {
                    throw $e;
                }
                return $apiKey;
            }
        )
    );

$GLOBALS['TL_DCA']['tl_settings']['fields']['schubwerk_tracking_base'] =
    array(
        'inputType' => 'text',
        'eval' => array('mandatory' => false, 'tl_class' => 'clr'),
    );

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('schubwerk_legend', null, \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE, true)
    ->addField('schubwerk_tracking_project_id', 'schubwerk_legend')
    ->addField('schubwerk_tracking_base', 'schubwerk_legend')
    ->applyToPalette('default', 'tl_settings');

