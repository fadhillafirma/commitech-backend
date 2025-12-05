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

            // Create session dengan device info (7 days expiry)
            $token = $user->createToken(
                $request->device_name,
                ['*'],
                now()->addDays(7)
            )->plainTextToken;
            
            // Get session ID from token
            $sessionId = explode('|', $token)[0] ?? null;
            
            if ($sessionId) {
                // Update session dengan device info
                DB::table('sessions')
                    ->where('id', $sessionId)
                    ->update([
                        'device_name' => $request->device_name,
                        'device_type' => $request->device_type,
                        'device_id' => $request->device_id,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'location' => $this->getLocationFromIp($request->ip()),
                        'last_activity' => now()->timestamp,
                        'created_at' => now()->timestamp, // CRITICAL: Set created_at saat register
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
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'device_name' => 'required|string',
                'device_type' => 'required|string',
                'device_id' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], 422);
            }

            // CRITICAL: Check if device already has session
            $existingSession = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('device_id', $request->device_id)
                ->first();
            
            if ($existingSession) {
                // Device sudah punya session, update saja
                // CRITICAL: Jangan update created_at, biarkan tetap dari login pertama
                DB::table('sessions')
                    ->where('id', $existingSession->id)
                    ->update([
                        'device_name' => $request->device_name,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'location' => $this->getLocationFromIp($request->ip()),
                        'last_activity' => now()->timestamp,
                    ]);
                
                $token = $existingSession->id;
            } else {
                // Device baru, create new session (7 days expiry)
                $token = $user->createToken(
                    $request->device_name,
                    ['*'],
                    now()->addDays(7)
                )->plainTextToken;
                
                // Get session ID from token
                $sessionId = explode('|', $token)[0] ?? null;
                
                if ($sessionId) {
                    // Update session record dengan device info
                    DB::table('sessions')
                        ->where('id', $sessionId)
                        ->update([
                            'device_name' => $request->device_name,
                            'device_type' => $request->device_type,
                            'device_id' => $request->device_id,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'location' => $this->getLocationFromIp($request->ip()),
                            'last_activity' => now()->timestamp,
                            'created_at' => now()->timestamp, // CRITICAL: Set created_at saat login baru
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
