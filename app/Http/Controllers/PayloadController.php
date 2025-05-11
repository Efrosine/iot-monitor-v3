<?php

namespace App\Http\Controllers;

use App\Models\Payload;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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