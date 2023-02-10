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
        'searchFields' => 'title,ics_url,note,last_message',
        'iconfile' => 'EXT:calendarize_external/Resources/Public/Icons/tx_calendarizeexternal_domain_model_calendar.gif'
    ],
    'types' => [
        '1' => ['showitem' => 'title, ics_url, note, scheduler_interval, last_run, last_message, error_count,
               --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime,
               --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, description'],
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
                'default' => 6,
                'eval' => 'int',
                'itemsProcFunc' => \Verdigado\CalendarizeExternal\Hooks\ItemsProcFunc::class . '->user_schedulerIntervalSelect',
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1
            ]
        ],
        'last_message' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.last_message',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'readOnly' => true
            ],
        ],
        'last_run' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.last_run',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
            ],
        ],
        'error_count' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.error_count',
            'config' => [
                'eval' => 'int,Verdigado\CalendarizeExternal\Evaluation\ErrorCountReset',
                'renderType' => 'input',
                'size' => 1,
                'type' => 'input',
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:calendarize_external/Resources/Private/Language/locallang_db.xlf:tx_calendarizeexternal_domain_model_calendar.description',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'default' => ''
            ],
        ],

    ],
];
