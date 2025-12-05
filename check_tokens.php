<?php

/**
 * Check personal_access_tokens table structure
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING PERSONAL ACCESS TOKENS ===\n\n";

$columns = Schema::getColumnListing('personal_access_tokens');
print_r($columns);

echo "\n\n=== SAMPLE TOKEN ===\n";
$token = DB::table('personal_access_tokens')->orderBy('id', 'desc')->first();
print_r($token);
