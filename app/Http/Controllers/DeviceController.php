<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = Device::select('deviceId', 'name', 'type')->get();
        return response()->json($devices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'deviceId' => 'required|string|unique:devices,deviceId',
            'name' => 'required|string|max:255',
            'type' => 'string|in:actuator,sensor,camera',
        ]);

        $device = Device::create($validated);

        return response()->json([
            'message' => 'Device created successfully',
            'data' => $device
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($deviceId)
    {
        $deviceData = Device::select('deviceId', 'name', 'type')
            ->where('deviceId', $deviceId)
            ->firstOrFail();

        return response()->json($deviceData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $deviceId)
    {
        $device = Device::where('deviceId', $deviceId)->firstOrFail();

        // Validate the request data
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255'
        ]);

        $device->update($validated);

        return response()->json([
            'message' => 'Device updated successfully',
            'newName' => $device->name
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($deviceId)
    {
        $device = Device::where('deviceId', $deviceId)->firstOrFail();

        $device->delete();
        return response()->json([
            'message' => 'Device deleted successfully'
        ]);
    }
}