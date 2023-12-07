<?php

declare(strict_types=1);

namespace Verdigado\CalendarizeExternal\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class ResetLastrunCommand extends Command
{

    protected $scheduleRanges;

    public function __construct() {

        $this->scheduleRanges = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('calendarize_external', 'scheduleRanges');
        if (empty($this->scheduleRanges)) {
            $this->scheduleRanges = "2,6";
        }
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Set last_run fields to zero')
            ->addOption(
                'schedule',
                's',
                InputOption::VALUE_OPTIONAL,
                'The frequency in hours must be one of: ' . $this->scheduleRanges . " \r\n"
                . "Hint: You can set the schedule ranges in the extension configuration.\r\n"
            )
            ->addOption(
                'pages',
                'p',
                InputOption::VALUE_OPTIONAL,
                "One page uid or multiple page uids, comma separated.\n"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $table = 'tx_calendarizeexternal_domain_model_calendar';

        $schedule = $input->getOption('schedule');
        $scheduleRanges = (explode(',', $this->scheduleRanges));
        if (MathUtility::canBeInterpretedAsInteger($schedule)) {
            $useschedule = 0;
            foreach ($scheduleRanges as $scheduleRange) {
                if ($schedule == $scheduleRange) {
                    $useschedule = $scheduleRange;
                } else {
                    continue;
                }
            }
            if ($useschedule > 0) {
              $io->text('Reset only external calendars with schedule range ' . $useschedule . 'h.');
            } else {
              $io->error('Wrong schedule, use one of ' . $this->scheduleRanges . ' or omit this parameter.');
              return 1;
            }
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $queryBuilder = $connection->createQueryBuilder();

        $constraints = $queryBuilder->expr()->lt('error_count', $queryBuilder->createNamedParameter("11", \PDO::PARAM_INT));

        $pages = $input->getOption('pages');
        if (!empty($pages)) {
            $constraints .= ' AND ' . $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($pages, \PDO::PARAM_STR));
        }

        if ($useschedule > 0) {
            $constraints .= ' AND ' . $queryBuilder->expr()->eq('scheduler_interval', $queryBuilder->createNamedParameter((int)$useschedule, \PDO::PARAM_INT));
        }

        $statement = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where( $constraints )
            ->execute();

        // loop thru all external calendar records
        $extcalcount = 0;
        while ($record = $statement->fetch()) {
            // reset record
            $connection->update(
                $table,
                ['last_message' => '', 'last_run' => 0, 'error_count' => 0, 'md5' => ''],
                ['uid' => $record['uid']]
            );
            ++$extcalcount;
        }
        $io->text('Successful reset of ' . $extcalcount . ' external calendar(s).');
        return 0;
    }
}
