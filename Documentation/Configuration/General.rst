..  include:: /Includes.rst.txt
..  index:: Configuration
..  _configuration-general:

=====================
General configuration
=====================

There are no TypoScript or TSconfig settings in this extension.

After installation, you may adjust :ref:`scheduler hour ranges for editors <extensionSettings.scheduleRanges>`,
which default to be selectable at 2h or 6h intervals.

..  index::
    Configuration; Workflow
    Configuration; Typical
..  _configuration_basics:

Basic workflow
=============

*  create an `external calendar record`, specifying the url of
   the ICS file and the frequency of the import

*  (admin only) for each available :ref:`hour range <extensionSettings.scheduleRanges>`,
   create a new "Execute console commands" scheduler entry

*  you can manually test using

   .. code-block:: bash

      calendarize-external:cal-import 2 --since='90 days'

   Here, for all `external calendar records` having a scheduler interval of <= 2 hours
   the events from the corresponding url will be imported to the page, where
   its `external calendar record` exists.

On the first run (i.e. `Time of last run [last_run] is 0`), all events from the last two
years will be imported.

On all consecutive runs, events specified by `--since` will be (re)imported, here: last 90 days.

The result of the last run will be recorded in the `Last message [last_message]`
field (readonly).

If an error occured, the `Number of runs with errors ... [error_count]` field
will be incremented. If there have been counted more than 10 consecutive errors, the scheduled
ICS import for this record is not executed. This prevents recurring access to
wrong or too old ICS URLs.

On save, the `error_count` field is reset to `0`, assuming the (former wrong) url has been fixed. So,
the imports start again.


..  _configuration_typical:

Typical usage
===============

*   you will use this extension, if editors shall be able to schedule ICS imports
*   this makes sense especially for multi domain instances of TYPO3, where the domain
    owner has no admin rights and/or cannot use the :ref:`scheduler module <ext_scheduler:Introduction>`
*   using the scheduler interval setting within selectable :ref:`hour ranges <extensionSettings.scheduleRanges>`,
    you minimize the load by running seldom changed ICS calendars not so often
*   imports will only be run, if the ICS calendar url/file contents has been changed (checked by md5 hash)
*   only on first import, all older events in ICS file are imported (up to 2 years)
*   on all other import runs: admins may specify, how old changes should be (re)imported
*   failing imports will not be run after 10 or more errors occured
