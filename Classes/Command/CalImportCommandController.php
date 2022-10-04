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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
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
     * ImportCommandController constructor.
     *
     * @param ICalServiceInterface $iCalService
     * @param EventDispatcherInterface $eventDispatcher
     * @param IndexerService $indexerService
     */
    public function __construct(
        ICalServiceInterface     $iCalService,
        EventDispatcherInterface $eventDispatcher,
        IndexerService           $indexerService,
        ICalUrlService           $iCalUrlService
    )
    {
        $this->iCalService = $iCalService;
        $this->eventDispatcher = $eventDispatcher;
        $this->indexerService = $indexerService;
        $this->iCalUrlService = $iCalUrlService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Run all external calendar imports')
            ->addArgument(
                'schedule',
                InputArgument::REQUIRED,
                'The frequency must be one of: 2h, 12h, 1d, 2d'
            )
            ->addOption(
                'since',
                's',
                InputOption::VALUE_OPTIONAL,
                "Imports all events since the given date.\n"
                . 'Valid PHP date format e.g. "2014-04-14", "-10 days"' . "\n"
                . '(Note: use --since="-x days" syntax on the console)'
            );
    }

    /**
     * Executes the command to import all external calendars
     *
     * @param InputInterface $input
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
            $io->text('Run all external calendars which have set schedule to <=' . $schedule . 'h.');
        } else {
            $io->error('Schedule intervall in hours');

            return 1;

        }

        // Process skip
        $since = $input->getOption('since');
        $ignoreBeforeDate = null;
        if (null !== $since) {
            $ignoreBeforeDate = new \DateTime($since);
            $io->text('Skipping all events before ' . $ignoreBeforeDate->format(\DateTimeInterface::ATOM));
        }

        // @todo get all external calendars from database with icsfile and pid
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $statement = $queryBuilder
            ->select('uid', 'pid', 'title', 'ics_url', 'scheduler_interval', 'last_run', 'last_message')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('scheduler_interval', $queryBuilder->createNamedParameter((int)$schedule, \PDO::PARAM_INT))
            )
            ->execute();

        // loop thru all external calendars by external calendar record
        while ($record = $statement->fetch()) {
            // collect messages per record
            $msg = '';
            $errormsg = '';

            // Fetch external URI and write it to a temporary file
            $io->section('Start to checkout the calendar');

            try {
                // get icsCalendarUri from external calendar record
                $icalFile = $this->iCalUrlService->getOrCreateLocalFileForUrl($record['ics_url']);
            } catch (UnableToGetFileForUrlException $e) {
                $io->error('Invalid URL: ' . $e->getMessage());
                $errormsg .= "ical file: invalid url.\n";
                $connection->update(
                    $table,
                    ['last_message' => 'ERROR: '. $errormsg ],
                    // ['last_run' => date+time],
                    ['uid' => $record['uid']]
                );

                continue;
            }
            try {
                // Parse calendar
                $events = $this->iCalService->getEvents($icalFile);
            } catch (\Exception $e) {
                $io->error('Unable to process events');
                $io->writeln($e->getMessage());
                if ($io->isVerbose()) {
                    $io->writeln($e->getTraceAsString());
                }

                $errormsg .= 'Unable to process events: ' . $e->getMessage();
                $connection->update(
                    $table,
                    ['last_message' => 'ERROR: \n' . $errormsg],
                    // ['last_run' => date+time],
                    ['uid' => $record['uid']]
                );
                continue;
            } finally {
                // Remove temporary file
                GeneralUtility::unlink_tempfile($icalFile);
            }
            // @todo write last run and last message back to record

            $io->text('Found ' . \count($events) . ' events in ' . $record['title'] . ' on page ' . $record['pid']);
            $msg .= 'Found ' . \count($events) . ' events. \n';

            $io->section('Send ImportSingleIcalEvent for each event');
            $io->progressStart(\count($events));

            $skipCount = $dispatchCount = 0;
            foreach ($events as $event) {
                // Skip events before given date
                if (($event->getEndDate() ?? $event->getStartDate()) < $ignoreBeforeDate) {
                    $io->progressAdvance();
                    ++$skipCount;
                    continue;
                }
                // @todo get pid from external calendar record
                $this->eventDispatcher->dispatch(new ImportSingleIcalEvent($event, $record['pid']));
                ++$dispatchCount;
                $io->progressAdvance();
            }
            $io->progressFinish();

            $io->text('Dispatched ' . $dispatchCount . ' Events');
            $io->text('Skipped ' . $skipCount . ' Events');
            $msg .= 'Dispatched ' . $dispatchCount . ' Events\n';
            $msg .= 'Skipped ' . $skipCount . ' Events';
            $connection->update(
                $table,
                ['last_message' => $msg],
                // ['last_run' => date+time],
                ['uid' => $record['uid']]
            );

        }
        // after all calendar imports run reindex events
        // @todo make active
      //  $io->section('Run Reindex process after import');
     //   $this->indexerService->reindexAll();


        return 0;
    }
}
