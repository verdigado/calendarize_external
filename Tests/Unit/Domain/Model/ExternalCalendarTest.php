<?php

declare(strict_types=1);

namespace Verdigado\CalendarizeExternal\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 *
 * @author Falko Trojahn <support@verdigado.net>
 */
class ExternalCalendarTest extends UnitTestCase
{
    /**
     * @var \Verdigado\CalendarizeExternal\Domain\Model\ExternalCalendar|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \Verdigado\CalendarizeExternal\Domain\Model\ExternalCalendar::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetTitleReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    public function testSetTitleForStringSetsTitle(): void
    {
        $this->subject->setTitle('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('title'));
    }

    public function testGetIcsUrlReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getIcsUrl()
        );
    }

    public function testSetIcsUrlForStringSetsIcsUrl(): void
    {
        $this->subject->setIcsUrl('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('icsUrl'));
    }

    public function testGetNoteReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getNote()
        );
    }

    public function testSetNoteForStringSetsNote(): void
    {
        $this->subject->setNote('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('note'));
    }

    public function testGetSchedulerIntervalReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getSchedulerInterval()
        );
    }

    public function testSetSchedulerIntervalForIntSetsSchedulerInterval(): void
    {
        $this->subject->setSchedulerInterval(12);

        self::assertEquals(12, $this->subject->_get('scheduler_interval'));
    }
}
