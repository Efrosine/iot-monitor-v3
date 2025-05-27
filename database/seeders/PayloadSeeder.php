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
            // [
            //     'deviceId' => 'DEV002',
            //     'data' => json_encode([
            //         'type' => 'temperature',
            //         'value' => 23.8,
            //         'unit' => 'celsius'
            //     ]),
            // ],
            [
                'deviceId' => 'DEV003',
                'data' => json_encode([
                    'type' => 'humidity',
                    'value' => 65,
                    'unit' => 'percent'
                ]),
            ],
            // [
            //     'deviceId' => 'DEV004',
            //     'data' => json_encode([
            //         'type' => 'humidity',
            //         'value' => 68,
            //         'unit' => 'percent'
            //     ]),
            // ],
            [
                'deviceId' => 'DEV005',
                'data' => json_encode([
                    'type' => 'soil_moisture',
                    'value' => 45,
                    'unit' => 'percent'
                ]),
            ],
            [
                'deviceId' => 'DEV006',
                'data' => json_encode([
                    'type' => 'soil_moisture',
                    'value' => 42,
                    'unit' => 'percent'
                ]),
            ],
            [
                'deviceId' => 'DEV007',
                'data' => json_encode([
                    'type' => 'soil_moisture',
                    'value' => 48,
                    'unit' => 'percent'
                ]),
            ],
            [
                'deviceId' => 'DEV008',
                'data' => json_encode([
                    'type' => 'soil_moisture',
                    'value' => 50,
                    'unit' => 'percent'
                ]),
            ],
            [
                'deviceId' => 'DEV009',
                'data' => json_encode([
                    'type' => 'light_level',
                    'value' => 800,
                    'unit' => 'lux'
                ]),
            ],
            [
                'deviceId' => 'DEV010',
                'data' => json_encode([
                    'type' => 'light_level',
                    'value' => 750,
                    'unit' => 'lux'
                ]),
            ],
            [
                'deviceId' => 'DEV011',
                'data' => json_encode([
                    'status' => 'off'
                ]),
            ],
            [
                'deviceId' => 'DEV012',
                'data' => json_encode([
                    'status' => 'off'
                ]),
            ],
            [
                'deviceId' => 'DEV013',
                'data' => json_encode([
                    'status' => 'off'
                ]),
            ],
            [
                'deviceId' => 'DEV014',
                'data' => json_encode([
                    'status' => 'off'
                ]),
            ],
            // [
            //     'deviceId' => 'DEV015',
            //     'data' => json_encode([
            //         'url' => 'https://example.com/front-area-feed'
            //     ]),
            // ],
            [
                'deviceId' => 'DEV016',
                'data' => json_encode([
                    'url' => 'https://example.com/back-area-feed'
                ]),
            ],
            [
                'deviceId' => 'DEV017',
                'data' => json_encode([
                    'status' => 'off',
                    'value' => 0,
                ]),
            ],
            [
                'deviceId' => 'DEV018',
                'data' => json_encode([
                    'status' => 'off'
                ]),
            ],
            
        ];

        foreach ($payloads as $payload) {
            Payload::create($payload);
        }
    }
}