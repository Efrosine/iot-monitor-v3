<?php

namespace App\Listeners;

use App\Events\newHistoryEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use App\Models\Payload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\TurnOffDevice;

class ActuatorListener36 implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;
    public $uniqueFor = 60;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * The unique ID for the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return $this->event->deviceId;
    }

    /**
     * Calculate mean value for a specific sensor type
     * 
     * @param string $sensorType Type of sensor (Temperature, Humidity, Soil, Light)
     * @param \Illuminate\Support\Collection $devicesSensor Collection of all sensor devices
     * @return array Returns associative array with mean, sum, and count values
     */
    private function calculateSensorMean(string $sensorType, $devicesSensor): array
    {
        $sensors = $devicesSensor->filter(function($device) use ($sensorType) {
            return Str::contains($device->name, $sensorType);
        });

        $mean = 0;
        $sum = 0;
        $min = PHP_FLOAT_MAX; // Initialize min to the maximum float value
        
        $sensors->each(function($sensor) use (&$sum, &$mean, &$min) {
            $data = Payload::select('data')->where('deviceId', $sensor->deviceId)->latest()->first();
            if ($data) {
                Log::info('data for ' . $sensor->deviceId . ': ' . $data->data);
                $dataDecoded = json_decode($data->data);
                if ($dataDecoded && isset($dataDecoded->value)) {
                    $value = $dataDecoded->value;
                    $sum += $value;
                    
                    // Track minimum value
                    if ($value < $min) {
                        $min = $value;
                    }
                }
            }
        });

        // Only calculate mean if we have sensors
        if ($sensors->count() > 0) {
            $mean = $sum / $sensors->count();
        }
         // If no sensors had valid values, reset min
        if ($min === PHP_FLOAT_MAX) {
            $min = 29;
        }

        Log::info($sensorType . ' sensors count: ' . $sensors->count());
        Log::info('sum ' . $sensorType . ': ' . $sum);
        Log::info('mean ' . $sensorType . ': ' . $mean);
        Log::info('min ' . $sensorType . ': ' . $min);

        return [
            'mean' => $mean,
            'sum' => $sum,
            'count' => $sensors->count()
            ,'min' => $min
        ];
    }

    /**
     * Handle the event.
     */
    public function handle(newHistoryEvent $event): void
    {
        // Handle the event
        $deviceId = $event->deviceId;
        $devicesSensor = Device::select('deviceId', 'name')->where('type', 'sensor')->get();
        $currentDevice = Device::where('deviceId', $deviceId)->first();

        if($currentDevice->type != 'sensor'){
            return;
        }

        // Check temperature and time for AC control
        if (Str::contains($currentDevice->name, 'Temperature')) {
            $tempData = $this->calculateSensorMean('Temperature', $devicesSensor);
            
            // Get current hour (24-hour format)
            $currentHour = (int)date('H');
            
            // Define night hours (10 PM - 6 AM: 22-23, 0-6)
            $isNightTime = ($currentHour >= 22 || $currentHour < 6);
            
            Log::info('Current time: ' . date('H:i') . ' (' . ($isNightTime ? 'Night' : 'Day') . ')');
            
            if ($tempData) {
                if ($isNightTime) {
                    // Night time settings (on at 22°C, off at 17°C)
                    if ($tempData['min'] > 22) {
                        Log::info('Night time - Temperature is high, turning on AC.');
                        Artisan::call('ac:toggle', [
                            'deviceId' => 'DEV017
                            ',
                            'status' => 'on',
                            'value' => 17
                        ]);
                    } else if ($tempData['min'] < 17) {
                        Log::info('Night time - Temperature is low, turning off AC.');
                        Artisan::call('ac:toggle', [
                            'deviceId' => 'DEV017
                            ',
                            'status' => 'off'
                        ]);
                    }
                } else {
                    // Day time settings (on at 26°C, off at 20°C)
                    if ($tempData['min'] > 26) {
                        Log::info('Day time - Temperature is high, turning on AC.');
                        Artisan::call('ac:toggle', [
                            'deviceId' => 'DEV017
                            ',
                            'status' => 'on',
                            'value' => 20
                        ]);
                    } else if ($tempData['min'] < 20) {
                        Log::info('Day time - Temperature is low, turning off AC.');
                        Artisan::call('ac:toggle', [
                            'deviceId' => 'DEV017
                            ',
                            'status' => 'off'
                        ]);
                    }
                }
            }
        } elseif (Str::contains($currentDevice->name, 'Humidity')){ 
            // Log::info('is a humidity sensor');
            $humidityData = $this->calculateSensorMean('Humidity', $devicesSensor);
           if ($humidityData && $humidityData['mean'] < 40) {
            Log::info('Humidity is high, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV012',
                '--on' => true,
            ]);
           }else{
            Log::info('Humidity is normal, no action taken.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV012',
                '--off' => true,
            ]);
           }
           if ($humidityData && $humidityData['mean'] > 80) {
            Log::info('Humidity is high, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV011',
                '--on' => true,
            ]);
           }else{
            Log::info('Humidity is normal, no action taken.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV011',
                '--off' => true,
            ]);
           }
            
        }elseif (Str::contains($currentDevice->name, 'Soil')){ 
            // Log::info('is a soil sensor');
            $soilData = $this->calculateSensorMean('Soil', $devicesSensor);
           if ($soilData && $soilData['mean'] < 70) {
            Log::info('Soil moisture is low, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV013',
                '--on' => true,
            ]);
            TurnOffDevice::dispatch('DEV013')->delay(now()->addSeconds(5));
           }elseif ($soilData && $soilData['mean'] > 80) {
            Log::info('Soil moisture is enough, turning off the device.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV013',
                '--off' => true,
            ]);
           }else {
            Log::info('Soil moisture is normal, no action taken.');
           }
            
        }elseif (Str::contains($currentDevice->name, 'Light')){ 
            // Log::info('is a light sensor');
            $lightData = $this->calculateSensorMean('Light', $devicesSensor);
        //       if ($lightData && $lightData['mean'] < 50) {
        //     Log::info('Light is low, turning on the device.');
        //     Artisan::call('device:toggle', [
        //         'deviceId' => 'DEV014',
        //         '--on' => true,
        //     ]);
        //    }else{
        //     Log::info('Light is normal, no action taken.');
        //      Artisan::call('device:toggle', [
        //         'deviceId' => 'DEV014',
        //         '--off' => true,
        //     ]);
        //    }
        }else{
            // Log::info('is not a sensor');
            return;
        }
    }
}
//temp uper 26 ac on under 18 off
//hum under 40 mist(12) under 80 kipas(11)
//soil under 50 pump upper 60 off(13)

