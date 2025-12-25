<?php


require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sessionId = $argv[1] ?? null;

if (!$sessionId) {
    echo "Usage: php check_session.php <session_id>\n";
    echo "Example: php check_session.php 48\n";
    exit(1);
}

echo "=== CHECKING SESSION ID: $sessionId ===\n\n";

$session = DB::table('sessions')
    ->where('id', $sessionId)
    ->first();

if (!$session) {
    echo "❌ SESSION NOT FOUND!\n";
    echo "\nSession ID $sessionId tidak ada di database.\n";
    echo "\nSOLUTION:\n";
    echo "1. Logout dari app\n";
    echo "2. Login lagi\n";
    echo "3. Check token baru dari Logcat\n";
    exit(1);
}

echo "✅ SESSION FOUND!\n\n";
echo "Session Details:\n";
echo "  ID: " . $session->id . "\n";
echo "  User ID: " . $session->user_id . "\n";
echo "  Device Name: " . ($session->device_name ?? 'NULL') . "\n";
echo "  Device Type: " . ($session->device_type ?? 'NULL') . "\n";
echo "  Device ID: " . ($session->device_id ?? 'NULL') . "\n";
echo "  IP Address: " . ($session->ip_address ?? 'NULL') . "\n";
echo "  Location: " . ($session->location ?? 'NULL') . "\n";
echo "  Last Activity: " . $session->last_activity . " (" . date('Y-m-d H:i:s', $session->last_activity) . ")\n";
echo "  Created At: " . ($session->created_at ?? 'NULL');

if ($session->created_at) {
    echo " (" . date('Y-m-d H:i:s', $session->created_at) . ")\n";
    
    $now = time();
    $daysSinceCreated = floor(($now - $session->created_at) / (24 * 60 * 60));
    $daysRemaining = 7 - $daysSinceCreated;
    
    echo "\n";
    echo "Expiry Info:\n";
    echo "  Days Since Created: $daysSinceCreated days\n";
    echo "  Days Remaining: $daysRemaining days\n";
    echo "  Expires At: " . date('Y-m-d H:i:s', $session->created_at + (7 * 24 * 60 * 60)) . "\n";
    
    if ($daysSinceCreated >= 7) {
        echo "\n❌ SESSION EXPIRED! (> 7 days)\n";
    } else {
        echo "\n✅ SESSION VALID! (< 7 days)\n";
    }
} else {
    echo " ❌ NULL - OLD SESSION!\n";
    echo "\nWARNING: This session was created before migration.\n";
    echo "SOLUTION: Logout & login lagi untuk create fresh session.\n";
}

echo "\n";

// Get user info
$user = DB::table('users')
    ->where('id', $session->user_id)
    ->select('id', 'name', 'email')
    ->first();

if ($user) {
    echo "User Info:\n";
    echo "  ID: " . $user->id . "\n";
    echo "  Name: " . $user->name . "\n";
    echo "  Email: " . $user->email . "\n";
}

echo "\n=== END ===\n";
