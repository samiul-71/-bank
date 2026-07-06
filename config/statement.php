<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statement Branding
    |--------------------------------------------------------------------------
    | These three lines print at the top of every generated statement.
    | Change them (or override via .env) to brand the statement for your
    | own solution — the layout stays identical, only the wording differs.
    */
    'institution' => env('STATEMENT_INSTITUTION', 'United Commercial Bank PLC'),
    'branch'      => env('STATEMENT_BRANCH', 'Gausul Azam Avenue Branch'),
    'title'       => env('STATEMENT_TITLE', 'Account Statement'),

    /*
    | Footer notes printed at the very end of the statement (bottom-left),
    | mirroring the reference document. Edit freely — fully dynamic.
    */
    'notes' => [
        'This report is provided by ' . rtrim(env('STATEMENT_INSTITUTION', 'United Commercial Bank PLC'), '.') . '.',
        'Remember, this is a system generated report and do not use it for official use.',
    ],

    /*
    | PDF document properties (the fields macOS "Get Info" / Adobe show under
    | Author, Content Creator and Encoding software). Set to match the
    | reference statement; override via .env if you brand your own.
    */
    'meta' => [
        'title'    => env('STATEMENT_META_TITLE', 'Account Statement'),
        'author'   => env('STATEMENT_META_AUTHOR', 'JasperReports Library version 6.4.0'),
        'creator'  => env('STATEMENT_META_CREATOR', 'JasperReports Library version 6.4.0'),
        'producer' => env('STATEMENT_META_PRODUCER', 'iText 2.1.7 by 1T3XT'),
    ],
];
