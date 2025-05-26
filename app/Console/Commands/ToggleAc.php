<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\PayloadController;

class ToggleAc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ac:toggle {deviceId} {status} {value?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle AC device on or off with optional value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $deviceId = $this->argument('deviceId');
        $status = $this->argument('status');
        $value = $this->argument('value');
        
        // Validate status (should be either 'on' or 'off')
        if (!in_array($status, ['on', 'off'])) {
            $this->error('Status must be either "on" or "off"');
            Log::error("Invalid AC status provided for device {$deviceId}: {$status}");
            return 1;
        }

        // If status is off, set value to 0
        if ($status === 'off') {
            $value = 0;
        }
        // Only validate value when status is on
        else if (!is_numeric($value) || $value < 16 || $value > 30) {
            $this->error('Value must be a number between 16 and 30');
            Log::error("Invalid AC value provided for device {$deviceId}: {$value}");
            return 1;
        }
        
        $payloadController = app()->make(PayloadController::class);
        
        // Create a request with the payload data
        $request = new Request();
        $request->replace([
            'data' => [
                'status' => $status,
                'value' => (int)$value
            ]
        ]);

        $response = $payloadController->update($request, $deviceId);
        
        // Output result based on status
        if ($status === 'on') {
            $this->info("AC device {$deviceId} turned on with temperature {$value}°C");
            Log::info("AC device {$deviceId} turned on with temperature {$value}°C");
        } else {
            $this->info("AC device {$deviceId} turned off");
            Log::info("AC device {$deviceId} turned off");
        }

        // Log the response
        // Log::info("API Response: " . json_encode($response));
        return 0;
    }
}
