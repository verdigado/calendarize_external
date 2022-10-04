<?php

namespace Verdigado\CalendarizeExternal\Hooks;

/**
 * This file is part of the "calendarize_external" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
// @todo cleanup start
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
// @todo cleanup end
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Userfunc to render scheduler range selectbox
 */
class ItemsProcFunc
{

    /**
     * Generate a select box of schedule hour ranges to select
     *
     * @param array $params
     */
    public function user_schedulerIntervalSelect(&$params): void
    {
        $setting = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('calendarize_external', 'scheduleRanges');
        $items = (explode(',', $setting));
        if (empty($items[0])) {
            $items = array(2, 6);
        }
        foreach ($items as $item) {
            $params['items'][] = [$item . 'h', $item];
        }
    }

}
