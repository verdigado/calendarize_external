<?php

/**
 * Import.
 */

declare(strict_types=1);

namespace Verdigado\CalendarizeExternal\Command;

use DateTime;
use DateTimeInterface;
use TYPO3\CMS\Core\Database\Connection;
use Exception;
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
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class CalImportCommandController extends Command
{
    /**
     * @var ScheduleRanges
     */
    protected $scheduleRanges;

    /**
     * ImportCommandController constructor.
     */
    public function __construct(
        protected ICalServiceInterface $iCalService,
        protected EventDispatcherInterface $eventDispatcher,
        protected IndexerService $indexerService,
        protected ICalUrlService $iCalUrlService
    ) {
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
                'pid',
                'p',
                InputOption::VALUE_OPTIONAL,
                "Imports events for a given pid.\n"
                . 'Multiple pages may be comma separated' . "\n"
                . '(Note: use --pid="20,25" syntax on the console)'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                "Force import, ignore md5 check of ical file.\n"
                . "Best combined using -p --pid for certain page. \n"
                . "Use -f or --force \n"
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
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $table = 'tx_calendarizeexternal_domain_model_calendar';

        $usepids = [];
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

            if ($pids = $input->getOption('pid')) {
                $usepids = explode(',', (string) $pids);
                $io->text('Run calendars on page(s) ' . $pids . ' which have set schedule range between ' . $schedulemin . ' and <=' . $schedule . 'h, not on hidden or deleted pages.');
            } else {
                $io->text('Run all external calendars which have set schedule range between ' . $schedulemin . ' and <=' . $schedule . 'h, not on hidden or deleted pages.');
            }
        } else {
            $io->error('Schedule intervall in hours is missing.');

            return 1;
        }

        // Process skip
        $since = $input->getOption('since');
        $reindex = $input->getOption('reindex');
        $force = $input->getOption('force') ?? false;
        $ignoreBeforeDate = null;
        $ignoreTwoYearsBeforeDate = new DateTime('-2 years');
        $msgsince = '';
        if (null !== $since) {
            $ignoreBeforeDate = new DateTime('-' . ltrim((string) $since, '-'));
            $io->text('Skipping all events before ' . $ignoreBeforeDate->format(DateTimeInterface::ATOM));
            $msgsince = $ignoreBeforeDate->format('d-m-y H:i');
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder
            ->select('uid', 'pid', 'title', 'ics_url', 'scheduler_interval', 'last_run', 'last_message', 'error_count', 'md5')
            ->from($table)
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gt('scheduler_interval', $queryBuilder->createNamedParameter((int)$schedulemin, Connection::PARAM_INT)),
                    $queryBuilder->expr()->lte('scheduler_interval', $queryBuilder->createNamedParameter((int)$schedule, Connection::PARAM_INT))
                )
            )
            ->andWhere(
                ' 1 = 1 ' . ($usepids === [] ? '' : 'AND ' . $queryBuilder->expr()->in('pid', (array)$usepids))
            )
            ->execute();

        $pageQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        // loop thru all external calendars by external calendar record
        while ($record = $statement->fetch()) {
            // collect messages per record
            $msg = '';
            $errormsg = '';
            $now = new DateTime();
            $lastrun = $now->getTimestamp();
            $io->section('Start to checkout the calendar ' . $record['uid'] . ' on page: ' . $record['pid']);

            $rootLineUtility = new RootlineUtility($record['pid']);
            try {
                $rootline = $rootLineUtility->get();
            } catch (Exception $e) {
                $io->warning("Not running: record is on deleted page " . $record['pid'] . ".");
                break;
            }

            $hiddenpage = false;
            $deletedpage = false;
            foreach ($rootline as $page) {
                $pageQueryBuilder->getRestrictions()
                    ->removeAll();
                $pageQueryBuilder
                    ->select('uid', 'deleted', 'hidden', 'is_siteroot')
                    ->from('pages')
                    ->where(
                        $pageQueryBuilder->expr()->eq('uid', $pageQueryBuilder->createNamedParameter($page['uid'], Connection::PARAM_INT))
                    );

                $pages = $pageQueryBuilder->execute()->fetch(0);
                if ($pages['deleted'] == 1) {
                    $deletedpage = $page['uid'];
                    break;
                }

                if ($pages['hidden'] == 1) {
                    $hiddenpage = $page['uid'];
                    break;
                }

                if ($pages['is_siteroot']) {
                    break;
                }
            }

            if ($deletedpage) {
                $io->warning('Not running: record is ' . ($deletedpage == $record['pid'] ? "on" : "under") . " deleted page " . $deletedpage . ".");
                continue;
            }

            if ($hiddenpage) {
                $io->warning('Not running: record is ' . ($hiddenpage == $record['pid'] ? "on" : "under" ) . " hidden page " . $hiddenpage . ".");
                continue;
            }

            $errorcount = $record['error_count'];
            if ($errorcount > 10) {
                // do not run, has to be cleared manually in Backend-record
                $io->warning('Not running: error count is:' . $errorcount);
                continue;
            }

            $ignoreDate = $ignoreBeforeDate; // from --since
            if ($force && $usepids !== [] || 0 == $record['last_run']) {
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

            if (
                !$force && !empty($record['md5']) && ($md5 !== '' && $md5 !== '0') && $md5 == $record['md5']
                && 0 != $record['last_run']
            ) {
                $io->text('ical file has not been changed (md5) - not importing');
                // Remove temporary file
                GeneralUtility::unlink_tempfile($icalFile);
                continue;
            }

            try {
                // Parse calendar
                $events = $this->iCalService->getEvents($icalFile);
            } catch (Exception $e) {
                $io->error('Unable to process events');
                $io->writeln('Url: ' . htmlspecialchars((string) $record['ics_url']));
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
            $skipCount = 0;
            $dispatchCount = 0;
            $exceptionCount = 0;
            foreach ($events as $event) {
                // Skip events before given date, on first run import <= -2 years
                if (($event->getEndDate() ?? $event->getStartDate()) < $ignoreDate) {
                    $io->progressAdvance();
                    ++$skipCount;
                    continue;
                }

                try {
                    $this->eventDispatcher->dispatch(new ImportSingleIcalEvent($event, $record['pid']));
                } catch (Exception $e) {
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
            $msg .= "Dispatched {$dispatchCount} events\r\n";
            $msg .= sprintf('Skipped  %d events', $skipCount);
            if ($exceptionCount > 0) {
                $msg .= $exceptionCount . ' events had errors';
                // @todo event errors count as one error ?
                ++$errorcount;
            }

            $msg .= ((0 == $record['last_run']) ? ' not within last two years (first run only).' : ($msgsince ? ' before ' . $msgsince : '')) . "\r\n";
            $connection->update(
                $table,
                ['last_message' => $msg, 'last_run' => $lastrun, 'error_count' => $errorcount, 'md5' => $md5],
                ['uid' => $record['uid']]
            );
            $cacheManager->flushCachesByTag('tx_calendarize_domain_model_event_' . $record['pid']);
        }

        // after all calendar imports run reindex events
        if ($reindex) {
            $io->section('Running reindex process after import');
            $this->indexerService->reindexAll();
        }

        return 0;
    }
}
