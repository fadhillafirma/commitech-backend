# Login Credentials

## Default Test User

After running the database seeder, you can login with:

- **Email:** `test@example.com`
- **Password:** `password`

## To Reset/Create Test User

Run the seeder:
```bash
php artisan db:seed --class=DatabaseSeeder
```

Or create manually:
```bash
php artisan tinker
```

Then in tinker:
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);
```

## Troubleshooting Login Issues

1. **Make sure user exists:**
   ```bash
   php artisan tinker
   User::where('email', 'test@example.com')->first();
   ```

2. **Reset password:**
   ```bash
   php artisan tinker
   $user = User::where('email', 'test@example.com')->first();
   $user->password = Hash::make('password');
   $user->save();
   ```

3. **Check logs:**
   - Login attempts are logged in `storage/logs/laravel.log`
   - Look for "Login attempt failed" or "Login successful" messages

4. **Common Issues:**
   - Email has whitespace: The code automatically trims email
   - Case sensitivity: Email comparison is case-insensitive
   - Password mismatch: Make sure you're using exactly `password` (lowercase)

