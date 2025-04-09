<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Calendarize External',
    'description' => 'allow editors to create records for external ics imports',
    'category' => 'be',
    'author' => 'Falko Trojahn',
    'author_email' => 'support@verdigado.net',
    'state' => 'alpha',
    'version' => '1.1.2',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.3-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
