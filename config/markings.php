<?php

return [
    // The path to the folder where your types are located using glob patterns.
    // Multiple locations are supported.
    'types_paths' => ['app/Models'],

    // The path to the folder where your events are located using glob patterns.
    // Multiple locations are supported
    'events_paths' => ['app/Events'],

    // Exclude the following files from being synked to the server.
    'exclude_files' => [
        // DontSyncThis::class,
    ],

    'api_url' => 'https://markings.io/api/v1/',
    'api_token' => env('MARKINGS_API_TOKEN'),
];
