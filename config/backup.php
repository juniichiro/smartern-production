<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Schedule Configuration
    |--------------------------------------------------------------------------
    |
    | Defines the automated backup schedule configuration. This is used by
    | the console scheduler in routes/console.php and displayed in the UI.
    |
    */
    'schedule' => [
        'enabled' => true,
        'time' => '19:00', // 24-hour HH:MM format
        'frequency' => 'daily',
        'display' => 'Daily at 7:00 PM',
        'editable' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Health & Staleness Thresholds
    |--------------------------------------------------------------------------
    |
    | Hours since the last successful backup before triggering warning or
    | critical alerts on the system configuration health banner.
    |
    */
    'health' => [
        'warn_after_hours' => 36,
        'critical_after_hours' => 72,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Snapshot Rules
    |--------------------------------------------------------------------------
    |
    | Constraints matching UploadBackupRequest for snapshot uploads.
    |
    */
    'upload' => [
        'max_size_kb' => 102400, // 100MB
        'allowed_extensions' => ['zip'],
        'allowed_mimes' => ['application/zip', 'application/x-zip-compressed'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | If an automated retention policy is configured, specify it here.
    | If null/disabled, retention notes will be omitted from the UI.
    |
    */
    'retention' => [
        'enabled' => false,
        'keep_backups' => null,
    ],
];
