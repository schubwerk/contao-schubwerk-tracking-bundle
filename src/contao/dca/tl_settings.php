<?php

$GLOBALS['TL_DCA']['tl_settings']['fields']['schubwerk_tracking_project_id'] =
    array(
        'inputType' => 'text',
        'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
    );

$GLOBALS['TL_DCA']['tl_settings']['fields']['schubwerk_tracking_track_events'] =
    array(
        'inputType' => 'checkbox',
        'eval' => array('mandatory' => false, 'tl_class' => 'w50'),
    );

$GLOBALS['TL_DCA']['tl_settings']['fields']['schubwerk_tracking_base'] =
    array(
        'inputType' => 'text',
        'eval' => array('mandatory' => false, 'tl_class' => 'clr'),
    );

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('schubwerk_legend', null, \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE, true)
    ->addField('schubwerk_tracking_project_id', 'schubwerk_legend')
    ->addField('schubwerk_tracking_track_events', 'schubwerk_legend')
    ->addField('schubwerk_tracking_base', 'schubwerk_legend')
    ->applyToPalette('default', 'tl_settings');

