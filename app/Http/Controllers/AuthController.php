<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user - INSTAGRAM STYLE
     * 
     * - Create user account
     * - Create session dengan device info
     * - Session expire 7 days
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'device_name' => 'required|string',
                'device_type' => 'required|string',
                'device_id' => 'required|string',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create token dengan device info (7 days expiry)
            $token = $user->createToken(
                $request->device_name,
                ['*'],
                now()->addDays(7)
            )->plainTextToken;
            
            // Get token ID from token
            $tokenId = explode('|', $token)[0] ?? null;
            
            if ($tokenId) {
                // Update token record dengan device info
                DB::table('personal_access_tokens')
                    ->where('id', $tokenId)
                    ->update([
                        'device_type' => $request->device_type,
                        'device_id' => $request->device_id,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'location' => $this->getLocationFromIp($request->ip()),
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user - INSTAGRAM STYLE
     * 
     * - Multi-device support (tidak delete old tokens)
     * - Check existing session untuk device yang sama
     * - Update existing session atau create new
     * - Session expire 7 days
     */
    public function login(Request $request)
    {
        try {
            // Log incoming request untuk debugging
            \Log::info('Login attempt', [
                'email' => $request->email,
                'has_password' => !empty($request->password),
                'device_name' => $request->device_name ?? 'missing',
                'device_type' => $request->device_type ?? 'missing',
                'device_id' => $request->device_id ?? 'missing',
            ]);
            
            // Trim email untuk menghilangkan whitespace dan pastikan tidak kosong
            $email = trim($request->email ?? '');
            
            // Validasi email tidak kosong setelah trim
            if (empty($email)) {
                \Log::warning('Login failed: Empty email after trim');
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'email' => ['The email field is required.']
                    ]
                ], 422);
            }
            
            // Merge email yang sudah di-trim ke request
            $request->merge(['email' => $email]);
            
            try {
                $request->validate([
                    'email' => 'required|email', // Validasi email standard
                    'password' => 'required',
                    'device_name' => 'required|string',
                    'device_type' => 'required|string',
                    'device_id' => 'required|string',
                ]);
            } catch (ValidationException $e) {
                \Log::warning('Login validation failed', ['errors' => $e->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            // Gunakan email yang sudah di-trim untuk query (case-insensitive)
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

            if (!$user) {
                \Log::warning('Login attempt failed: User not found', ['email' => $email]);
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], 422);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                \Log::warning('Login attempt failed: Invalid password', [
                    'email' => $email,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], 422);
            }
            
            \Log::info('Login successful', ['email' => $email, 'user_id' => $user->id]);

            // CRITICAL: Check if device already has token di personal_access_tokens
            $existingToken = DB::table('personal_access_tokens')
                ->where('tokenable_id', $user->id)
                ->where('tokenable_type', User::class)
                ->where('device_id', $request->device_id)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();
            
            if ($existingToken) {
                // Device sudah punya token yang masih valid, update saja
                DB::table('personal_access_tokens')
                    ->where('id', $existingToken->id)
                    ->update([
                        'name' => $request->device_name,
                        'device_type' => $request->device_type,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'location' => $this->getLocationFromIp($request->ip()),
                        'last_used_at' => now(),
                    ]);
                
                // Get token hash untuk reconstruct token
                // Note: Kita tidak bisa reconstruct token dari hash, jadi kita perlu buat token baru
                // atau simpan token plain di database (tidak recommended untuk security)
                // Solusi: Delete token lama dan buat baru dengan device yang sama
                DB::table('personal_access_tokens')->where('id', $existingToken->id)->delete();
                
                // Create new token untuk device yang sama
                $token = $user->createToken(
                    $request->device_name,
                    ['*'],
                    now()->addDays(7)
                )->plainTextToken;
                
                // Update token dengan device info
                $tokenId = explode('|', $token)[0] ?? null;
                if ($tokenId) {
                    DB::table('personal_access_tokens')
                        ->where('id', $tokenId)
                        ->update([
                            'device_type' => $request->device_type,
                            'device_id' => $request->device_id,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'location' => $this->getLocationFromIp($request->ip()),
                        ]);
                }
            } else {
                // Device baru, create new token (7 days expiry)
                $token = $user->createToken(
                    $request->device_name,
                    ['*'],
                    now()->addDays(7)
                )->plainTextToken;
                
                // Get token ID from token
                $tokenId = explode('|', $token)[0] ?? null;
                
                if ($tokenId) {
                    // Update token record dengan device info
                    DB::table('personal_access_tokens')
                        ->where('id', $tokenId)
                        ->update([
                            'device_type' => $request->device_type,
                            'device_id' => $request->device_id,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'location' => $this->getLocationFromIp($request->ip()),
                        ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ]
            ]
        ]);
    }
    
    /**
     * Get location from IP address
     * 
     * INSTAGRAM-STYLE:
     * - Track user location untuk security
     * - Show di Active Sessions
     * - Detect suspicious login
     * 
     * Uses ip-api.com (free, no API key required)
     */
    private function getLocationFromIp($ip)
    {
        // Skip for local IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return 'Local';
        }
        
        try {
            // Call ip-api.com (free, 45 req/min limit)
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['city']) && isset($data['country'])) {
                    return "{$data['city']}, {$data['country']}";
                }
            }
        } catch (\Exception $e) {
            // Ignore error, return Unknown
        }
        
        return 'Unknown';
    }
}
