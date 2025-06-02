<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\PayloadController;
use App\Models\Device;

class ToggleDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:toggle {deviceId} {--on} {--off} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle a device on or off (respects auto_mode unless --force is used)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceId = $this->argument('deviceId');
        $status = $this->option('on') ? 'on' : ($this->option('off') ? 'off' : null);
        $force = $this->option('force');

        // Check if device exists and get its auto_mode status
        $device = Device::where('deviceId', $deviceId)->first();

        if (!$device) {
            $this->error("Device {$deviceId} not found");
            Log::error("Device {$deviceId} not found when trying to toggle");
            return 1;
        }

        // Check if device is in manual mode and we're not forcing the action
        if (!$device->auto_mode && !$force) {
            $this->warn("Device {$deviceId} is in manual mode, skipping scheduled action");
            Log::info("Device {$deviceId} is in manual mode, skipping scheduled action");
            return 0;
        }

        $payloadController = app()->make(PayloadController::class);

        // Create a request with the payload data
        $request = new Request();
        $request->replace([
            'data' => [
                'status' => $status
            ]
        ]);

        $response = $payloadController->update($request, $deviceId);
        // Log::info("response: {$response}");
        if ($this->option('on')) {
            // Turn device on
            $this->info("Device {$deviceId} turned on");
            Log::info("Device {$deviceId} turned on");
        } elseif ($this->option('off')) {
            // Turn device off
            $this->info("Device {$deviceId} turned off");
            Log::info("Device {$deviceId} turned off");
        } else {
            $this->error('You must specify either --on or --off');
            Log::error('You must specify either --on or --off');
            return 1;
        }

        // Log the response
        // Log::info("API Response: " . json_encode($response));
        return 0;
    }
}