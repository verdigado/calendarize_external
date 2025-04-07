<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die();

(static function (): void {
    ExtensionManagementUtility::addLLrefForTCAdescr('tx_calendarizeexternal_domain_model_calendar', 'EXT:calendarize_external/Resources/Private/Language/locallang_csh_tx_calendarizeexternal_domain_model_calendar.xlf');
    ExtensionManagementUtility::allowTableOnStandardPages('tx_calendarizeexternal_domain_model_calendar');
})();
