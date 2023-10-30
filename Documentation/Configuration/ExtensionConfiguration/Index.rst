.. include:: /Includes.txt

.. _extensionConfiguration:

=======================
Extension Configuration
=======================

Some general settings can be configured in the Extension Configuration.

#. Go to :guilabel:`Admin Tools > Settings > Extension Configuration`
#. Choose :guilabel:`calendarize_external`

The settings are described here in detail:

.. only:: html

   .. contents:: Properties
        :local:
        :depth: 2


Basic
=====

.. _extensionSettings.scheduleRanges:

Set the selectable hour ranges for scheduler
--------------------------------------------

.. confval:: maintenance.scheduleRanges

   :type: string
   :Default: "2,6"

   In external calendar records, the editor may select from these ranges,
   how often the ICS import is run (in hours between).
   Example: "2,6,24,48,72"

.. figure:: /Images/SelectScheduleInterval.png
   :class: with-shadow
   :alt: Edit external calendar record
   :width: 500px

   The editor can select from these intervals.
