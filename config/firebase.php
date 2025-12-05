<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account JSON file.
    | Download this file from Firebase Console → Project Settings → Service Accounts
    |
    */
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/serviceAccountKey.json')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase project ID from Firebase Console
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL (Optional)
    |--------------------------------------------------------------------------
    |
    | Only needed if you use Realtime Database
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', null),
];
