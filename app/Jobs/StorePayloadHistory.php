<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Payload;
use App\Models\Device;
use App\Events\newHistoryEvent;
use Carbon\Carbon;

class StorePayloadHistory
{
    use Dispatchable;

    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(Payload $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $deviceId = $this->payload->deviceId;
        $tableName = 'history_' . str_replace('-', '_', $deviceId);
        $currentTime = now();

        // Fetch device information to check if it's an actuator or AC
        $device = Device::where('deviceId', $deviceId)->first();
        $isActuator = $device && ($device->type === 'actuator' || $device->type === 'ac');

        // Check if history table exists
        if (!Schema::hasTable($tableName)) {
            // Create new history table for this device
            try {
                Schema::create($tableName, function (Blueprint $table) {
                    $table->id();
                    $table->json('data');
                    $table->timestamps();
                });

                // If table was just created, always insert the first record
                $shouldInsert = true;
            } catch (\Exception $e) {
                Log::error("Failed to create history table: {$tableName}. Error: " . $e->getMessage());
                return;
            }
        } else {
            // Get the last record to compare data
            $lastRecord = DB::table($tableName)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastRecord) {
                // No records exist yet, so insert
                $shouldInsert = true;
            } else {
                if ($isActuator) {
                    // For actuators and ACs, compare the data with the last record
                    $lastData = json_decode($lastRecord->data, true);
                    $currentData = json_decode($this->payload->data, true);

                    // Compare data to see if it's different
                    $shouldInsert = $lastData != $currentData;

                    if ($shouldInsert) {
                        Log::info("Actuator/AC data changed, inserting new record for deviceId: {$deviceId}");
                    } else {
                        Log::info("Actuator/AC data unchanged, skipping insertion for deviceId: {$deviceId}");
                    }
                } else {
                    // For non-actuator devices, check when the last record was inserted (time-based)
                    $lastCreatedAt = Carbon::parse($lastRecord->created_at);
                    $diffInSeconds = $lastCreatedAt->diffInSeconds($currentTime);
                    Log::info("Time since last record: {$diffInSeconds} seconds");
                    // Only insert if 30 seconds or more have passed
                    $shouldInsert = $diffInSeconds >= 30;
                }
            }
        }

        log::info("Should insert: " . ($shouldInsert ? 'true' : 'false') . " for deviceId: {$deviceId} into table: {$tableName}");

        // Insert payload data into history table if conditions are met
        if ($shouldInsert) {
            try {
                // Get the data (handle cases where it might be already encoded or not)
                $data = $this->payload->data;

                DB::table($tableName)->insert([
                    'data' => $data,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ]);

                // Get the recent history entries for this device
                $historyLimit = 10; // Adjust this limit as needed
                $history = DB::table($tableName)->select('data', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit($historyLimit)
                    ->get()->map(function ($entry) {
                        return [
                            'data' => json_decode($entry->data),
                            'created_at' => $entry->created_at,
                        ];
                    });

                // Broadcast the history event
                event(new newHistoryEvent($history, $deviceId));
                Log::info("History event broadcasted for deviceId: {$deviceId}");

            } catch (\Exception $e) {
                Log::error("Failed to insert record into history table: {$tableName}. Error: " . $e->getMessage());
            }
        }
    }
}