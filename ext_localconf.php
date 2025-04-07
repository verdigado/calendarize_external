<?php

use Verdigado\CalendarizeExternal\Evaluation\ErrorCountReset;

// Register the class to be available in 'eval' of TCA
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][ErrorCountReset::class] = '';
