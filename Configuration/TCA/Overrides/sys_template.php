<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || exit();

ExtensionManagementUtility::addStaticFile('calendarize_external', 'Configuration/TypoScript', 'Calendarize External');
