..  include:: /Includes.rst.txt
..  highlight:: php

..  _developer:

================
Admin corner
================

*  you may test the ICS import using console commands:

   .. code-block:: bash

      $ vendor/bin/typo3 calendarize-external:cal-import
      calendarize-external:cal-import [-s|--since [SINCE]] [-r|--reindex] [--] <schedule>

   You must specify the schedule like 2, 6 or what ever you have configured in :ref:`hour ranges <extensionSettings.scheduleRanges>`.
   Then, all records containing ICS imports are run for this schedule interval. E.g.

   *  2: all external calendar records containing <=2 in "scheduler_interval" will be processed
   *  6: all external calendar records containing 2 <= 6 in "scheduler_interval" will be processed

   Using `-r` calendarize reindex is started after the ICS import.

   .. figure:: /Images/ConsoleCommandOutput.png
      :class: with-shadow
      :alt: Console command output
      :width: 500px

      Console run shows progress of imports and possible states

*  create scheduler entries for all schedule range hours, see :ref:`configuration_basics`

   .. figure:: /Images/SchedulerEntriesExample.png
      :class: with-shadow
      :alt: Scheduler entries example
      :width: 500px

      Example of scheduler entries in big multidomain instance

   Here, the `-r` for reindex is omitted. Instead, an extra reindex command is scheduled.
