<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Monitor Dashboard</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body class="min-h-screen bg-base-200">
    <div class="container mx-auto p-4">
        <header class="mb-6">
            <h1 class="text-3xl font-bold text-center my-4">IoT Device Monitor</h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="device-cards">
            <!-- Device cards will be added here dynamically -->
            <div class="skeleton h-64 w-full"></div>
            <div class="skeleton h-64 w-full"></div>
            <div class="skeleton h-64 w-full"></div>
        </div>
    </div>

</body>
<script>
    // Fetch devices when the page loads
    document.addEventListener('DOMContentLoaded', function () {
        fetchDevices();
    });

    // Fetch devices from API
    function fetchDevices() {
        fetch('/api/devices')
            .then(response => response.json())
            .then(devices => {
                const deviceCardsContainer = document.getElementById('device-cards');
                deviceCardsContainer.innerHTML = '';

                if (devices.length === 0) {
                    deviceCardsContainer.innerHTML = '<div class="col-span-full text-center p-6"><p class="text-lg text-neutral-500">No devices found.</p></div>';
                    return;
                }

                devices.forEach(device => {
                    const card = createDeviceCard(device);
                    deviceCardsContainer.appendChild(card);

                    // Set up Echo listener for this device
                    setupDeviceListener(device.deviceId);
                });
            })
            .catch(error => {
                console.error('Error fetching devices:', error);
                const deviceCardsContainer = document.getElementById('device-cards');
                deviceCardsContainer.innerHTML = '<div class="col-span-full text-center p-6"><p class="text-lg text-error">Error loading devices. Please try again later.</p></div>';
            });
    }

    // Create a card element for a device
    function createDeviceCard(device) {
        const card = document.createElement('div');
        card.className = 'card bg-base-100 shadow-xl hover:bg-base-200 cursor-pointer';
        card.id = `device-${device.deviceId}`;

        // Add click event to navigate to device detail page
        card.addEventListener('click', function () {
            window.location.href = `/devices/${device.deviceId}`;
        });

        // Create appropriate icon based on device type
        let icon = '';
        let statusBadge = '';

        if (device.type === 'sensor') {
            icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>';
        } else if (device.type === 'actuator') {
            icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>';
        } else {
            icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>';
        }

        // Create status badge
        statusBadge = '<div class="badge badge-primary badge-outline">Waiting for data</div>';

        card.innerHTML = `
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <h2 class="card-title">${device.name}</h2>
                    <div class="status status-success status-md"></div>
                </div>
                <div class="divider my-0"></div>
                <div class="flex items-center mb-2">
                    <div class="mr-2">${icon}</div>
                    <div>
                        <p class="text-sm opacity-70">Device ID: ${device.deviceId}</p>
                        <p class="text-sm opacity-70">Type: ${device.type}</p>
                    </div>
                </div>
                <div class="mt-2" id="device-data-${device.deviceId}">
                    ${statusBadge}
                    <p class="mt-2 text-sm">No data received yet</p>
                </div>
            </div>
        `;

        return card;
    }

    // Setup Echo listener for a specific device
    function setupDeviceListener(deviceId) {
        setTimeout(() => {
            // Listen to the device-specific channel
            window.Echo.channel(`device.${deviceId}`).listen('newHistoryEvent', (e) => {
                console.log(`Event received for device ${deviceId}:`, e);
                updateDeviceCard(deviceId, e);
            });
        }, 200);
    }

    // Update device card with new data
    function updateDeviceCard(deviceId, event) {
        const dataContainer = document.getElementById(`device-data-${deviceId}`);
        if (!dataContainer) return;

        if (event.history && event.history.length > 0) {
            const latestData = event.history[0].data;
            const timestamp = new Date(event.history[0].created_at).toLocaleString();

            let dataHtml = '<div class="badge badge-success">Active</div>';
            dataHtml += '<div class="mt-2">';

            // Display data properties
            for (const key in latestData) {
                if (Object.hasOwnProperty.call(latestData, key)) {
                    const value = latestData[key];
                    dataHtml += `<p class="text-sm"><span class="font-medium">${key}:</span> ${value}</p>`;
                }
            }

            dataHtml += `<p class="text-xs mt-1 opacity-60">Last updated: ${timestamp}</p>`;
            dataHtml += '</div>';

            dataContainer.innerHTML = dataHtml;

            // Update status indicator
            const card = document.getElementById(`device-${deviceId}`);
            if (card) {
                const statusIndicator = card.querySelector('.status');
                if (statusIndicator) {
                    statusIndicator.className = 'status status-success status-md';
                }
            }
        }
    }

    // Global history listener
    setTimeout(() => {
        // Listen to main history channel for all devices
        window.Echo.channel('history').listen('newHistoryEvent', (e) => {
            console.log('Global history event:', e);
            if (e.deviceId) {
                updateDeviceCard(e.deviceId, e);
            }
        });
    }, 200);
</script>

</html>