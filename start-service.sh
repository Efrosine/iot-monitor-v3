#!/bin/bash
cd /home/ghouse/project/iot-monitor-v3

# Start services
./vendor/bin/sail up -d
sleep 15  # Tunggu container siap
./vendor/bin/sail artisan reverb:start &
./vendor/bin/sail artisan queue:work &
./vendor/bin/sail artisan schedule:work &

# Pertahankan proses
exec tail -f /dev/null
