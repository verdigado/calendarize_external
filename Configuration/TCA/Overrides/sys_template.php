<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || exit;

ExtensionManagementUtility::addStaticFile('calendarize_external', 'Configuration/TypoScript', 'Calendarize External');
