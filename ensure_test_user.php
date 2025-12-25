<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Ensuring Test User Exists ===\n\n";

$user = User::where('email', 'test@example.com')->first();

if ($user) {
    echo "✓ User found: {$user->email}\n";
    echo "  Name: {$user->name}\n";
    echo "  ID: {$user->id}\n";

    $user->password = Hash::make('password');
    $user->save();
    echo "✓ Password reset to 'password'\n";

    if (Hash::check('password', $user->password)) {
        echo "✓ Password verification: OK\n";
    } else {
        echo "✗ Password verification: FAILED\n";
    }
} else {
    echo "User not found. Creating new user...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
    
    echo "✓ User created successfully:\n";
    echo "  Email: {$user->email}\n";
    echo "  Name: {$user->name}\n";
    echo "  Password: password\n";
}

echo "\n=== Login Credentials ===\n";
echo "Email: test@example.com\n";
echo "Password: password\n";
echo "\n✓ Ready to login!\n";

