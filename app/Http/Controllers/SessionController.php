<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionController extends Controller
{
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

        if ($token->expires_at && Carbon::now()->gt($token->expires_at)) {
            return response()->json([
                'isValid' => false,
                'message' => 'Session expired'
            ], 401);
        }
        
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
    
    public function getActiveSessions(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        
        $tokens = $user->tokens()->orderBy('last_used_at', 'desc')->get();
        
        $activeSessions = [];
        
        foreach ($tokens as $token) {
            $activeSessions[] = [
                'id' => (string) $token->id,
                'deviceName' => $token->name,
                'deviceType' => 'unknown',
                'ipAddress' => 'unknown',
                'location' => 'Unknown',
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
    
    public function revokeSession(Request $request, $sessionId)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        
        if ((string)$sessionId === (string)$currentToken->id) {
            return response()->json([
                'error' => 'Cannot revoke current session. Use logout instead.'
            ], 400);
        }
        
        $user->tokens()->where('id', $sessionId)->delete();
        
        return response()->json([
            'message' => 'Session revoked successfully'
        ]);
    }
    
    public function revokeOtherSessions(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        
        return response()->json([
            'message' => "Revoked other sessions successfully"
        ]);
    }
}
