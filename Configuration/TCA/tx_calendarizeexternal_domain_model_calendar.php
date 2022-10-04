<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,ics_url,note',
        'iconfile' => 'EXT:calendarize_external/Resources/Public/Icons/tx_calendarizeexternal_domain_model_calendar.gif'
    ],
    'types' => [
        '1' => ['showitem' => 'title, ics_url, note, scheduler_interval, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime'],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],

        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => ''
            ],
        ],
        'ics_url' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.ics_url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => ''
            ],
        ],
        'note' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.note',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'default' => ''
            ],
        ],
        'scheduler_interval' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.scheduler_interval
            ',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int',
                'default' => 0
            ]
        ],

    ],
];
