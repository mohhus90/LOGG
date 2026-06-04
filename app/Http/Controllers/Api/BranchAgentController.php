<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FingerprintDevice;
use App\Services\FingerprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchAgentController extends Controller
{
    public function push(Request $request): JsonResponse
    {
        $token = $request->header('X-Agent-Token');

        if (!$token) {
            return response()->json(['success' => false, 'error' => 'missing_token'], 401);
        }

        $device = FingerprintDevice::where('api_token', $token)
            ->where('status', 1)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'error' => 'invalid_token'], 401);
        }

        $validated = $request->validate([
            'logs'             => 'required|array|min:1',
            'logs.*.id'        => 'required',
            'logs.*.timestamp' => 'required|date',
            'logs.*.type'      => 'nullable|integer',
            'logs.*.state'     => 'nullable|integer',
        ]);

        $service = new FingerprintService();
        $result  = $service->saveLogs($device, $validated['logs'], 'agent');

        $device->update([
            'last_sync_at'      => now(),
            'last_sync_records' => $result['count'],
            'last_error'        => $result['error'],
            'status'            => $result['success'] ? 1 : 3,
        ]);

        return response()->json($result);
    }
}
