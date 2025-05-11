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
                'name' => 'Humidity Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV003',
                'name' => 'Light Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV004',
                'name' => 'Motion Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV005',
                'name' => 'Smart Light 1',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV006',
                'name' => 'Smart Fan 1',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV007',
                'name' => 'Air Quality Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV008',
                'name' => 'Water Valve 1',
                'type' => 'actuator',
            ],
            [
                'deviceId' => 'DEV009',
                'name' => 'Pressure Sensor 1',
                'type' => 'sensor',
            ],
            [
                'deviceId' => 'DEV010',
                'name' => 'Smart Lock 1',
                'type' => 'actuator',
            ],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        }
    }
}
