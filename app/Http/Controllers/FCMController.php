<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FCMController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:50',
            'device_name' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            DB::table('fcm_tokens')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'fcm_token' => $request->fcm_token
                ],
                [
                    'device_type' => $request->device_type ?? 'android',
                    'device_name' => $request->device_name,
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deleted = DB::table('fcm_tokens')
                ->where('fcm_token', $request->fcm_token)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'FCM token unregistered successfully',
                'deleted' => $deleted > 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unregister FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDevices()
    {
        try {
            $user = Auth::user();

            $devices = DB::table('fcm_tokens')
                ->where('user_id', $user->id)
                ->select('id', 'device_type', 'device_name', 'created_at', 'updated_at')
                ->get();

            return response()->json([
                'success' => true,
                'devices' => $devices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get devices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteDevice($id)
    {
        try {
            $user = Auth::user();

            $deleted = DB::table('fcm_tokens')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Device removed successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
