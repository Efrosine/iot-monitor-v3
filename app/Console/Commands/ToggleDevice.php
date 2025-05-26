<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\PayloadController;

class ToggleDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:toggle {deviceId} {--on} {--off}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle a device on or off';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceId = $this->argument('deviceId');
        $status = $this->option('on') ? 'on' : ($this->option('off') ? 'off' : null);
        
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