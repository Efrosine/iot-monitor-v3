<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Payload;
use Illuminate\Http\Request;

class ActuatorsController extends Controller
{
    /**
     * Display a listing of all actuator devices.
     */
    public function index()
    {
        $actuators = Device::select('deviceId', 'name')->where('type', 'actuator')->get();
        return response()->json($actuators);
    }

    /**
     * Display a specific actuator device.
     */
    public function show($id)
    {
        $actuator = Device::select('deviceId', 'name')->where('deviceId', $id)
            ->where('type', 'actuator')
            ->first();

        if (!$actuator) {
            return response()->json(['error' => 'Actuator not found'], 404);
        }

        return response()->json($actuator);
    }

    /**
     * Display all payloads for all actuator devices.
     */
    public function payloads()
    {
        $actuatorDevices = Device::where('type', 'actuator')->pluck('deviceId');

        $payloads = Payload::select('deviceId', 'data')
            ->whereIn('deviceId', $actuatorDevices)
            ->get()
            ->map(function ($payload) {
                return [
                    'deviceId' => $payload->deviceId,
                    'status' => json_decode($payload->data)->status,
                ];
            });

        return response()->json($payloads);
    }


}