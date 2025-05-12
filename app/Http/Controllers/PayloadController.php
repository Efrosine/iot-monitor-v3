<?php

namespace App\Http\Controllers;

use App\Events\newHistoryEvent;
use App\Models\Payload;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayloadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payloads = Payload::select('deviceId', 'data')->get()->map(function ($payload) {
            return [
                'deviceId' => $payload->deviceId,
                'data' => json_decode($payload->data),
            ];
        });

        return response()->json($payloads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $deviceId)
    {
        // Check if the device exists
        $device = Device::where('deviceId', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => 'Device not found',
                'message' => 'Cannot create payload for non-existent device'
            ], 404);
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'data' => 'required|array',
        ]);

        // Create a new payload
        $payload = Payload::create([
            'deviceId' => $deviceId,
            'data' => json_encode($validated['data'])
        ]);

        // Run the job immediately instead of dispatching to queue
        (new \App\Jobs\StorePayloadHistory($payload))->handle();

        return response()->json([
            'payload' => [
                'deviceId' => $payload->deviceId,
                'data' => json_decode($payload->data),
            ],
            'message' => 'Payload created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($deviceId)
    {
        $payloads = Payload::select('deviceId', 'data')
            ->where('deviceId', $deviceId)
            ->get()
            ->map(function ($payload) {
                return [
                    'deviceId' => $payload->deviceId,
                    'data' => json_decode($payload->data),
                ];
            });

        return response()->json($payloads);
    }

    /**
     * Get recent history for a specific device.
     * 
     * @param string $deviceId The ID of the device
     * @param int $limit The maximum number of records to return (default: 60)
     * @return \Illuminate\Http\JsonResponse
     */
    public function history($deviceId, $limit = 60)
    {
        // Check if the device exists
        $device = Device::where('deviceId', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => 'Device not found',
                'message' => 'Cannot retrieve history for non-existent device'
            ], 404);
        }

        // Format table name based on deviceId
        $tableName = 'history_' . str_replace('-', '_', $deviceId);

        // Check if history table exists
        if (!Schema::hasTable($tableName)) {
            return response()->json([
                'error' => 'No history available',
                'message' => 'No history data exists for this device'
            ], 404);
        }

        // Get the recent history entries, limited to the specified number
        $history = DB::table($tableName)->select('data', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()->map(function ($entry) {
                return [
                    'data' => json_decode($entry->data),
                    'created_at' => $entry->created_at,
                ];
            });
        // Dispatch an event for the new history entry
        event(new newHistoryEvent($history));
        return response()->json($history);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $deviceId)
    {
        // Check if the device exists
        $device = Device::where('deviceId', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'error' => 'Device not found',
                'message' => 'Cannot create payload for non-existent device'
            ], 404);
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'data' => 'required|array',
        ]);

        // This will update an existing payload or create a new one if it doesn't exist
        $payload = Payload::updateOrCreate(
            ['deviceId' => $deviceId],
            ['data' => json_encode($validated['data'])]
        );

        (new \App\Jobs\StorePayloadHistory($payload))->handle();

        return response()->json([
            'payload' => [
                'deviceId' => $payload->deviceId,
                'data' => json_decode($payload->data),
            ],
            'message' => $payload->wasRecentlyCreated ? 'Payload created successfully' : 'Payload updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payload $payload)
    {
        //
    }
}