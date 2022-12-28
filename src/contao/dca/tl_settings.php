<?php

$GLOBALS['TL_DCA']['tl_settings']['fields']['schubwerk_tracking_project_id'] =
    array(
        'inputType' => 'text',
        'eval' => array('mandatory' => true, 'helpwizard' => true, 'decodeEntities' => true, 'tl_class' => 'w50'),
        'explanation' => 'dateFormat'
    );

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('schubwerk_legend', null, \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE, true)
    ->addField('schubwerk_tracking_project_id', 'schubwerk_legend')
    ->applyToPalette('default', 'tl_settings');

