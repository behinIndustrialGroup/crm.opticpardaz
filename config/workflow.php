<?php

return [
    'caseStartValue' => 1000,
    'patterns' => [
        'customer_fullname',
        'device_name', 
        'device_serial_no'
    ],
    'caseNumberingPerCategory' => true,
    'caseNumberingPerProcess' => false,
    'inboxStatus' => [
        'new' => ['label' => 'new', 'color' => 'primary'],
        'opened' => ['label' => 'opened', 'color' => 'secondary'],
        'inProgress' => ['label' => 'inProgress', 'color' => 'warning'],
        'draft' => ['label' => 'draft', 'color' => 'info'],
        'canceled' => ['label' => 'canceled', 'color' => 'danger'],
        'done' => ['label' => 'done', 'color' => 'success'],
        'doneByOther' => ['label' => 'doneByOther', 'color' => 'success']
    ]
];
