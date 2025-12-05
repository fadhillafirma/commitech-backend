<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * SessionController - Manage server-side sessions
 * 
 * INSTAGRAM-STYLE SESSION MANAGEMENT (API VERSION):
 * - Sessions stored in personal_access_tokens table (Sanctum)
 * - Token validation via middleware
 * - Auto-expire based on expires_at field
 * - Support multi-device login dengan tracking
 */
class SessionController extends Controller
{
    /**
     * Check session validity
     * 
     * Endpoint: GET /api/session/check
     */
    public function checkSession(Request $request)
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        
        if (!$token) {
            return response()->json([
                'isValid' => false,
                'message' => 'No token provided'
            ], 401);
        }

        // Check if expired (Sanctum automatically checks expiration, but we can double check)
        if ($token->expires_at && Carbon::now()->gt($token->expires_at)) {
            return response()->json([
                'isValid' => false,
                'message' => 'Session expired'
            ], 401);
        }
        
        // Update last_used_at
        $token->forceFill([
            'last_used_at' => now(),
        ])->save();
        
        $expiresAt = $token->expires_at ? Carbon::parse($token->expires_at) : null;
        $daysRemaining = $expiresAt ? Carbon::now()->diffInDays($expiresAt) : null;
        
        return response()->json([
            'isValid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'daysRemaining' => (int) $daysRemaining,
            'expiresAt' => $expiresAt ? $expiresAt->toDateTimeString() : null
        ]);
    }
    
    /**
     * Get list of active sessions for current user
     * 
     * Endpoint: GET /api/session/list
     */
    public function getActiveSessions(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        
        // Get all tokens for this user
        $tokens = $user->tokens()->orderBy('last_used_at', 'desc')->get();
        
        $activeSessions = [];
        
        foreach ($tokens as $token) {
            $activeSessions[] = [
                'id' => (string) $token->id, // ID di personal_access_tokens adalah integer
                'deviceName' => $token->name, // Kita simpan device name di field 'name'
                'deviceType' => 'unknown', // Info ini perlu ditambah ke migration token jika ingin detail
                'ipAddress' => 'unknown', // Perlu ditambah ke migration token
                'location' => 'Unknown', // Perlu ditambah ke migration token
                'lastActivity' => $token->last_used_at ? Carbon::parse($token->last_used_at)->diffForHumans() : 'Never',
                'lastActivityTimestamp' => $token->last_used_at ? Carbon::parse($token->last_used_at)->timestamp : 0,
                'createdAt' => $token->created_at->format('d M Y H:i'),
                'expiresAt' => $token->expires_at ? Carbon::parse($token->expires_at)->format('d M Y Y H:i') : 'Never',
                'isCurrent' => $token->id === $currentToken->id,
            ];
        }
        
        return response()->json([
            'sessions' => $activeSessions,
            'totalSessions' => count($activeSessions)
        ]);
    }
    
    /**
     * Revoke specific session by ID
     * 
     * Endpoint: DELETE /api/session/{id}
     */
    public function revokeSession(Request $request, $sessionId)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        
        // Prevent revoking current session via this endpoint
        if ((string)$sessionId === (string)$currentToken->id) {
            return response()->json([
                'error' => 'Cannot revoke current session. Use logout instead.'
            ], 400);
        }
        
        // Delete token
        $user->tokens()->where('id', $sessionId)->delete();
        
        return response()->json([
            'message' => 'Session revoked successfully'
        ]);
    }
    
    /**
     * Revoke all other sessions except current
     * 
     * Endpoint: POST /api/session/revoke-others
     */
    public function revokeOtherSessions(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        
        // Delete all tokens except current
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        
        return response()->json([
            'message' => "Revoked other sessions successfully"
        ]);
    }
}
