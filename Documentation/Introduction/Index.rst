..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

..  tip::

    New to calendarize?

    Get an introduction: :ref:`ext_calendarize:gettingStarted`

    Find the ICS import documentation for ext:calendarize here: :ref:`Import ICS / ICal calendar <ext_calendarize:importIcs>`

..  _what-it-does:

What does it do?
================

This extension extends the usage of ext:calendarize regarding the ICS import:

*   makes it possible for TYPO3 editors to create records to schedule imports from
    urls and files
*   usually, only admins would be able to create scheduler entries to import
*   using ext:calendarize_external, editors create external calendar records and
*   admins create :ref:`ext:scheduler <ext_scheduler:Introduction>` entries: which collect editor's ICS import urls/files
    and run the ICS imports to the page with the `external calendar record`

The extension settings allow to provide different schedule cycles like 2h,6h, 24h
and so on, to be selected by the editors.

..  attention::

    There is a patch needed for calendarize:
    https://github.com/verdigado/calendarize/tree/restrict-icsimport-to-pid

    This allows to have same urls in different external calendar records
    so that all records will be imported into their respective pages.
    For tests or in multi domain environments this is essential.

..  _screenshots:

Screenshots
===========

The Editor can create `external calendar records` which are run by admin-created scheduler records.

.. figure:: /Images/BackendEditExternalCalendarRecord.png
   :class: with-shadow
   :alt: Edit external calendar record
   :width: 500px

    Backend view of the record.

In the backend, an editor creates records containing the ICS import url/file, scheduler
interval, title and notes, if needed.

