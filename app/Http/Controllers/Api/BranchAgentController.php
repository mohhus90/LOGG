<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FingerprintDevice;
use App\Services\FingerprintService;
use Carbon\Carbon;
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

        // معالجة السجلات تلقائياً فور الاستلام — تحويلها من fingerprint_logs إلى attendance
        $processResult = ['imported' => 0, 'missing' => 0, 'absent' => 0];
        if ($result['success']) {
            $dates = collect($validated['logs'])
                ->map(fn($l) => Carbon::parse($l['timestamp'])->format('Y-m-d'))
                ->unique()->sort()->values();

            if ($dates->isNotEmpty()) {
                $processResult = $service->processLogs(
                    $device->com_code,
                    $dates->first(),
                    $dates->last(),
                    false
                );
            }
        }

        $device->update([
            'last_sync_at'      => now(),
            'last_sync_records' => $result['count'],
            'last_error'        => $result['error'] ?? ($processResult['error'] ?? null),
            'status'            => $result['success'] ? 1 : 3,
        ]);

        return response()->json(array_merge($result, [
            'attendance_imported' => $processResult['imported']  ?? 0,
            'attendance_missing'  => $processResult['missing']   ?? 0,
            'attendance_absent'   => $processResult['absent']    ?? 0,
            'not_found_ids'       => $processResult['notFound']  ?? [],
        ]));
    }
}
