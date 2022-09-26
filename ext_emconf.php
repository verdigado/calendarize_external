<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'calendarize_external',
    'description' => 'Calendarize users can edit external calendars',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Verdigado\\CalendarizeExternal\\' => 'Classes/',
        ],
    ],
];
