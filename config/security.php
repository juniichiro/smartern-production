<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Isolation Forest Anomaly Detection Thresholds
    |--------------------------------------------------------------------------
    |
    | Defines the single source of truth for security anomaly classification.
    | - Normal:   score <= warning (default <= 0.50)
    | - Warning:  warning < score <= critical (default 0.50 < score <= 0.70)
    | - Critical: score > critical (default > 0.70)
    |
    */

    'thresholds' => [
        'warning' => (float) env('SECURITY_THRESHOLD_WARNING', 0.50),
        'critical' => (float) env('SECURITY_THRESHOLD_CRITICAL', 0.70),
    ],

];
