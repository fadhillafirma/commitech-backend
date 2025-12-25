<?php

return [
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/serviceAccountKey.json')),
    ],

    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    'database_url' => env('FIREBASE_DATABASE_URL', null),
];
