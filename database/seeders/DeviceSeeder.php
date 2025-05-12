<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            [
                'deviceId' => 'DEV001',
                'name' => 'Temperature Sensor',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV002',
                'name' => 'Air Humidity Sensor',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV003',
                'name' => 'Soil Moisture Sensor',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV004',
                'name' => 'Light Intensity Sensor',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV005',
                'name' => 'Fan Actuator',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV006',
                'name' => 'Mist Maker Actuator',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV007',
                'name' => 'Water Pump Actuator',
                'type' => 'actuator',
            ],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        }
    }
}
