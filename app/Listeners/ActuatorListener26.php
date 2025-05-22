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

class ActuatorListener26 implements ShouldQueue, ShouldBeUnique
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
        
        $sensors->each(function($sensor) use (&$sum) {
            $data = Payload::select('data')->where('deviceId', $sensor->deviceId)->latest()->first();
            if ($data) {
                Log::info('data for ' . $sensor->deviceId . ': ' . $data->data);
                $dataDecoded = json_decode($data->data);
                if ($dataDecoded && isset($dataDecoded->value)) {
                    $sum += $dataDecoded->value;
                }
            }
        });

        // Only calculate mean if we have sensors
        if ($sensors->count() > 0) {
            $mean = $sum / $sensors->count();
        }

        Log::info($sensorType . ' sensors count: ' . $sensors->count());
        Log::info('sum ' . $sensorType . ': ' . $sum);
        Log::info('mean ' . $sensorType . ': ' . $mean);

        return [
            'mean' => $mean,
            'sum' => $sum,
            'count' => $sensors->count()
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

        Log::info('sensors: ' . $devicesSensor);
        Log::info('current: ' . $currentDevice);

        if($currentDevice->type != 'sensor'){
            Log::info('not a sensor');
            return;
        }

        if(Str::contains($currentDevice->name, 'Temperature')){
            Log::info('is a temperature sensor');
            $tempData = $this->calculateSensorMean('Temperature', $devicesSensor);
           if ($tempData && $tempData['mean'] > 30) {
            Log::info('Temperature is high, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--on' => true,
            ]);
           }else{
            Log::info('Temperature is normal, no action taken.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--off' => true,
            ]);
           }
            
        }elseif (Str::contains($currentDevice->name, 'Humidity')){ 
            Log::info('is a humidity sensor');
            $humidityData = $this->calculateSensorMean('Humidity', $devicesSensor);
           if ($humidityData && $humidityData['mean'] > 70) {
            Log::info('Humidity is high, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--on' => true,
            ]);
           }else{
            Log::info('Humidity is normal, no action taken.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--off' => true,
            ]);
           }
            
        }elseif (Str::contains($currentDevice->name, 'Soil')){ 
            Log::info('is a soil sensor');
            $soilData = $this->calculateSensorMean('Soil', $devicesSensor);
           if ($soilData && $soilData['mean'] < 30) {
            Log::info('Soil moisture is low, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--on' => true,
            ]);
           }else{
            Log::info('Soil moisture is normal, no action taken.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--off' => true,
            ]);
           }
            
        }elseif (Str::contains($currentDevice->name, 'Light')){ 
            Log::info('is a light sensor');
            $lightData = $this->calculateSensorMean('Light', $devicesSensor);
              if ($lightData && $lightData['mean'] < 50) {
            Log::info('Light is low, turning on the device.');
            Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--on' => true,
            ]);
           }else{
            Log::info('Light is normal, no action taken.');
             Artisan::call('device:toggle', [
                'deviceId' => 'DEV014',
                '--off' => true,
            ]);
           }
        }else{
            Log::info('is not a sensor');
            return;
        }
    }
}
