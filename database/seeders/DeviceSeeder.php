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
                'name' => 'Temperature Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV002',
                'name' => 'Temperature Sensor 2',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV003',
                'name' => 'Air Humidity Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV004',
                'name' => 'Air Humidity Sensor 2',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV005',
                'name' => 'Soil Moisture Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV006',
                'name' => 'Soil Moisture Sensor 2',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV007',
                'name' => 'Soil Moisture Sensor 3',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV008',
                'name' => 'Soil Moisture Sensor 4',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV009',
                'name' => 'Light Intensity Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV010',
                'name' => 'Light Intensity Sensor 2',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV011',
                'name' => 'Fan Actuator',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV012',
                'name' => 'Mist Maker Actuator',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV0013',
                'name' => 'Water Pump Actuator',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV014',
                'name' => 'Growlight Actuator',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV015',
                'name' => 'Front Area Camera',
                'type' => 'camera',
            ],
            [
                'deviceId' => 'DEV016',
                'name' => 'Back Area Camera',
                'type' => 'camera',
            ],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        }
    }
}