<?php

declare(strict_types=1);

namespace Verdigado\CalendarizeExternal\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
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

    /**
     * @test
     */
    public function getTitleReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleForStringSetsTitle(): void
    {
        $this->subject->setTitle('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('title'));
    }

    /**
     * @test
     */
    public function getIcsUrlReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getIcsUrl()
        );
    }

    /**
     * @test
     */
    public function setIcsUrlForStringSetsIcsUrl(): void
    {
        $this->subject->setIcsUrl('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('icsUrl'));
    }

    /**
     * @test
     */
    public function getNoteReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getNote()
        );
    }

    /**
     * @test
     */
    public function setNoteForStringSetsNote(): void
    {
        $this->subject->setNote('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('note'));
    }

    /**
     * @test
     */
    public function getSchedulerIntervalReturnsInitialValueForInt(): void
    {
        self::assertSame(
            0,
            $this->subject->getSchedulerInterval()
        );
    }

    /**
     * @test
     */
    public function setSchedulerIntervalForIntSetsSchedulerInterval(): void
    {
        $this->subject->setSchedulerInterval(12);

        self::assertEquals(12, $this->subject->_get('scheduler_interval'));
    }
}
