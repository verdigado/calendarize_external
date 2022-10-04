<?php

declare(strict_types=1);

namespace Verdigado\CalendarizeExternal\Domain\Model;


/**
 * This file is part of the "Calendarize External" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Falko Trojahn <support@verdigado.net>, verdigado e.G.
 */

/**
 * ExternalCalendar
 */
class ExternalCalendar extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * title
     *
     * @var string
     */
    protected $title = '';

    /**
     * icsUrl
     *
     * @var string
     */
    protected $icsUrl = '';

    /**
     * note
     *
     * @var string
     */
    protected $note = '';

    /**
     * scheduler_interval
     *
     * @var int
     */
    protected $scheduler_interval = 0;

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns the icsUrl
     *
     * @return string $icsUrl
     */
    public function getIcsUrl()
    {
        return $this->icsUrl;
    }

    /**
     * Sets the icsUrl
     *
     * @param string $icsUrl
     * @return void
     */
    public function setIcsUrl(string $icsUrl)
    {
        $this->icsUrl = $icsUrl;
    }

    /**
     * Returns the note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Sets the note
     *
     * @param string $note
     * @return void
     */
    public function setNote(string $note)
    {
        $this->note = $note;
    }

    /**
     * Returns the scheduler_interval
     *
     * @return int $scheduler_interval
     */
    public function getSchedulerInterval()
    {
        return $this->scheduler_interval;
    }

    /**
     * Sets the scheduler_interval
     *
     * @param int $scheduler_interval
     * @return void
     */
    public function setSchedulerInterval(int $scheduler_interval)
    {
        $this->scheduler = $scheduler_interval;
    }
}
