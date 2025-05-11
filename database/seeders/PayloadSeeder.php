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
                'data' => json_encode(['temperature' => 25.5, 'unit' => 'celsius']),
            ],
            [
                'deviceId' => 'DEV002',
                'data' => json_encode(['humidity' => 65, 'unit' => 'percent']),
            ],
            [
                'deviceId' => 'DEV003',
                'data' => json_encode(['light_level' => 800, 'unit' => 'lux']),
            ],
            [
                'deviceId' => 'DEV004',
                'data' => json_encode(['motion_detected' => true, 'sensitivity' => 'high']),
            ],
            [
                'deviceId' => 'DEV005',
                'data' => json_encode(['status' => 'on', 'brightness' => 75]),
            ],
            [
                'deviceId' => 'DEV006',
                'data' => json_encode(['status' => 'off', 'speed' => 0]),
            ],
            [
                'deviceId' => 'DEV007',
                'data' => json_encode(['co2' => 450, 'tvoc' => 250, 'quality' => 'good']),
            ],
            [
                'deviceId' => 'DEV008',
                'data' => json_encode(['status' => 'closed', 'pressure' => 3.5]),
            ],
            [
                'deviceId' => 'DEV009',
                'data' => json_encode(['pressure' => 1013, 'unit' => 'hPa']),
            ],
            [
                'deviceId' => 'DEV010',
                'data' => json_encode(['status' => 'locked', 'battery' => 85]),
            ],
        ];

        foreach ($payloads as $payload) {
            Payload::create($payload);
        }
    }
}
