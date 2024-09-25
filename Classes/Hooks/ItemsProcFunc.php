<?php

namespace Verdigado\CalendarizeExternal\Hooks;

/*
 * This file is part of the "calendarize_external" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Userfunc to render scheduler range selectbox.
 */
class ItemsProcFunc
{
    /**
     * Generate a select box of schedule hour ranges to select.
     *
     * @param array<int, mixed> $params
     */
    public function user_schedulerIntervalSelect(&$params): void
    {
        $setting = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('calendarize_external', 'scheduleRanges');
        $items = $setting == '' ? [2, 6] : explode(',', (string)$setting);

        foreach ($items as $item) {
            $params['items'][] = [$item . 'h', $item];
        }
    }
}
