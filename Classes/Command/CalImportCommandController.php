<?php

/**
 * Import.
 */
declare(strict_types=1);

namespace Verdigado\CalendarizeExternal\Command;

use HDNET\Calendarize\Event\ImportSingleIcalEvent;
use HDNET\Calendarize\Exception\UnableToGetFileForUrlException;
use HDNET\Calendarize\Service\Ical\ICalServiceInterface;
use HDNET\Calendarize\Service\Ical\ICalUrlService;
use HDNET\Calendarize\Service\IndexerService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class CalImportCommandController extends Command
{
    /**
     * @var ICalServiceInterface
     */
    protected $iCalService;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var IndexerService
     */
    protected $indexerService;

    /**
     * @var ICalUrlService
     */
    protected $iCalUrlService;

    /**
     * @var ScheduleRanges
     */
    protected $scheduleRanges;

    /**
     * ImportCommandController constructor.
     *
     * @param ICalServiceInterface     $iCalService
     * @param EventDispatcherInterface $eventDispatcher
     * @param IndexerService           $indexerService
     */
    public function __construct(
        ICalServiceInterface $iCalService,
        EventDispatcherInterface $eventDispatcher,
        IndexerService $indexerService,
        ICalUrlService $iCalUrlService
    ) {
        $this->iCalService = $iCalService;
        $this->eventDispatcher = $eventDispatcher;
        $this->indexerService = $indexerService;
        $this->iCalUrlService = $iCalUrlService;

        $scheduleRanges = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('calendarize_external', 'scheduleRanges');
        $this->scheduleRanges = (explode(',', $scheduleRanges));
        if (empty($this->scheduleRanges[0])) {
            $this->scheduleRanges = [2, 6];
        }

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Run all external calendar imports')
            ->addArgument(
                'schedule',
                InputArgument::REQUIRED,
                'The frequency in hours must be one of: ' . implode(',', $this->scheduleRanges) . " \r\n"
                . "Hint: You can set the schedule ranges in the extension configuration.\r\n"
            )
            ->addOption(
                'since',
                's',
                InputOption::VALUE_OPTIONAL,
                "Imports all events since the given date.\n"
                . 'Valid PHP date format e.g. "2021-10-01", "-10 days"' . "\n"
                . '(Note: use --since="-x days" syntax on the console)'
            )
            ->addOption(
                'reindex',
                'r',
                InputOption::VALUE_NONE,
                "Do reindex after import of all entries.\n"
                . "You don't need this if you have another reindexer job running. \n"
                . "Use -r or --reindex \n"
            );
    }

    /**
     * Executes the command to import all external calendars.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $table = 'tx_calendarizeexternal_domain_model_calendar';

        $schedule = $input->getArgument('schedule');
        if (MathUtility::canBeInterpretedAsInteger($schedule)) {
            $schedulemin = 0;
            foreach ($this->scheduleRanges as $scheduleRange) {
                if ($schedule > $scheduleRange) {
                    $schedulemin = $scheduleRange;
                } else {
                    continue;
                }
            }
            $io->text('Run all external calendars which have set schedule range between ' . $schedulemin . ' and <=' . $schedule . 'h.');
        } else {
            $io->error('Schedule intervall in hours is missing.');

            return 1;
        }

        // Process skip
        $since = $input->getOption('since');
        $reindex = $input->getOption('reindex');
        $ignoreBeforeDate = null;
        $ignoreTwoYearsBeforeDate = new \DateTime('-2 years');
        $msgsince = '';
        if (null !== $since) {
            $ignoreBeforeDate = new \DateTime('-' . ltrim($since, '-'));
            $io->text('Skipping all events before ' . $ignoreBeforeDate->format(\DateTimeInterface::ATOM));
            $msgsince = $ignoreBeforeDate->format('d-m-y H:i');
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder
            ->select('uid', 'pid', 'title', 'ics_url', 'scheduler_interval', 'last_run', 'last_message', 'error_count', 'md5')
            ->from($table)
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gt('scheduler_interval', $queryBuilder->createNamedParameter((int)$schedulemin, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->lte('scheduler_interval', $queryBuilder->createNamedParameter((int)$schedule, \PDO::PARAM_INT))
                )
            )
            ->execute();

        // loop thru all external calendars by external calendar record
        while ($record = $statement->fetch()) {
            // collect messages per record
            $msg = '';
            $errormsg = '';
            $now = new \DateTime();
            $lastrun = $now->getTimestamp();
            $io->section('Start to checkout the calendar ' . $record['uid'] . ' on page: ' . $record['pid']);

            $errorcount = $record['error_count'];
            if ($errorcount > 10) {
                // do not run, has to be cleared manually in Backend-record
                $io->warning('Not running: error count is:' . $errorcount);
                continue;
            }
            $ignoreDate = $ignoreBeforeDate; // from --since
            if (0 == $record['last_run']) {
                $ignoreDate = $ignoreTwoYearsBeforeDate;  // default if not run
            }

            // Fetch external URI and write it to a temporary file
            try {
                // get icsCalendarUri from external calendar record
                $icalFile = $this->iCalUrlService->getOrCreateLocalFileForUrl($record['ics_url']);
                // @todo create md5 from content
                $contents = GeneralUtility::getURL($icalFile);
                $md5 = md5($contents);
            } catch (UnableToGetFileForUrlException $e) {
                $io->error('Invalid URL: ' . $e->getMessage());
                $errormsg .= "ical: invalid url.\r\n";
                ++$errorcount;
                $connection->update(
                    $table,
                    ['last_message' => "ERROR: \r\n" . $errormsg, 'last_run' => $lastrun, 'error_count' => $errorcount],
                    ['uid' => $record['uid']]
                );
                continue;
            }
            if (!empty($record['md5']) && !empty($md5) && $md5 == $record['md5']
                && 0 != $record['last_run']) {
                $io->text('ical file has not been changed (md5) - not importing');
                // Remove temporary file
                GeneralUtility::unlink_tempfile($icalFile);
                continue;
            }
            try {
                // Parse calendar
                $events = $this->iCalService->getEvents($icalFile);
            } catch (\Exception $e) {
                $io->error('Unable to process events');
                $io->writeln('Url: ' . htmlspecialchars($record['ics_url']));
                $io->writeln($e->getMessage());
                if ($io->isVerbose()) {
                    $io->writeln($e->getTraceAsString());
                }

                $errormsg .= 'Unable to process events: ' . $e->getMessage();
                ++$errorcount;
                $connection->update(
                    $table,
                    ['last_message' => "ERROR: \r\n" . $errormsg, 'last_run' => $lastrun, 'error_count' => $errorcount],
                    ['uid' => $record['uid']]
                );
                continue;
            } finally {
                // Remove temporary file
                GeneralUtility::unlink_tempfile($icalFile);
            }

            $io->text('Found ' . \count($events) . ' events in ' . $record['title']);
            $msg .= 'Found ' . \count($events) . " events. \r\n";

            $io->section('Send ImportSingleIcalEvent for each event');
            $io->progressStart(\count($events));

            $skipCount = $dispatchCount = $exceptionCount = 0;
            foreach ($events as $event) {
                // Skip events before given date, on first run import <= -2 years
                if (($event->getEndDate() ?? $event->getStartDate()) < $ignoreDate) {
                    $io->progressAdvance();
                    ++$skipCount;
                    continue;
                }
                try {
                    $this->eventDispatcher->dispatch(new ImportSingleIcalEvent($event, $record['pid']));
                } catch (\Exception $e) {
                    $io->error('Unable to process event:' . $record['pid']);
                    $io->writeln($e->getMessage());
                    if ($io->isVerbose()) {
                        $io->writeln($e->getTraceAsString());
                    }
                    ++$exceptionCount;
                    continue;
                }
                ++$dispatchCount;
                $io->progressAdvance();
            }
            $io->progressFinish();

            $io->text('Dispatched ' . $dispatchCount . ' events');
            $io->text('Skipped ' . $skipCount . ' events');
            $msg .= "Dispatched $dispatchCount events\r\n";
            $msg .= "Skipped  $skipCount events";
            if ($exceptionCount > 0) {
                $msg .= "$exceptionCount events had errors";
                // @todo event errors count as one error ?
                ++$errorcount;
            }
            $msg .= ((0 == $record['last_run']) ? ' not within last two years (first run only).' : ($msgsince ? ' before ' . $msgsince : '')) . "\r\n";
            $connection->update(
                $table,
                ['last_message' => $msg, 'last_run' => $lastrun, 'error_count' => $errorcount, 'md5' => $md5],
                ['uid' => $record['uid']]
            );
        }
        // after all calendar imports run reindex events
        if ($reindex) {
            $io->section('Running reindex process after import');
            $this->indexerService->reindexAll();
        }

        return 0;
    }
}
