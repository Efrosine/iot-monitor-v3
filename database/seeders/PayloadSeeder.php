<?php

namespace Database\Seeders;

use App\Models\Payload;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayloadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payloads = [
            [
                'deviceId' => 'DEV001',
                'data' => json_encode([
                    'type' => 'temperature',
                    'value' => 25.5,
                    'unit' => 'celsius'
                ]),
            ],
            [
                'deviceId' => 'DEV002',
                'data' => json_encode([
                    'type' => 'humidity',
                    'value' => 65,
                    'unit' => 'percent'
                ]),
            ],
            [
                'deviceId' => 'DEV003',
                'data' => json_encode([
                    'type' => 'soil_moisture',
                    'value' => 45,
                    'unit' => 'percent'
                ]),
            ],
            [
                'deviceId' => 'DEV004',
                'data' => json_encode([
                    'type' => 'light_level',
                    'value' => 800,
                    'unit' => 'lux'
                ]),
            ],
            [
                'deviceId' => 'DEV005',
                'data' => json_encode(['status' => 'off', 'speed' => 0]),
            ],
            [
                'deviceId' => 'DEV006',
                'data' => json_encode(['status' => 'off', 'intensity' => 0]),
            ],
            [
                'deviceId' => 'DEV007',
                'data' => json_encode(['status' => 'off', 'flow_rate' => 0]),
            ],
        ];

        foreach ($payloads as $payload) {
            Payload::create($payload);
        }
    }
}