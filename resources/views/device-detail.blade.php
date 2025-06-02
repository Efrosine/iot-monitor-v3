<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $device->name }} - IoT Monitor</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.3.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.2.0"></script>
</head>

<body class="min-h-screen bg-base-200">
    <div class="container mx-auto p-4">
        <div class="navbar bg-base-100 rounded-box shadow-md mb-6">
            <div class="navbar-start">
                <a href="/" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </a>
            </div>
            <div class="navbar-center">
                <h1 class="text-xl font-bold">{{ $device->name }}</h1>
            </div>
            <div class="navbar-end">
                <div class="badge badge-outline">{{ $device->type }}</div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title">Device Information</h2>
                <div class="divider my-0"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><span class="font-medium">Device ID:</span> {{ $device->deviceId }}</p>
                        <p><span class="font-medium">Type:</span> {{ $device->type }}</p>
                    </div>
                    <div>
                        <p><span class="font-medium">Created At:</span> {{ $device->created_at }}</p>
                        <p><span class="font-medium">Last Updated:</span> {{ $device->updated_at }}</p>
                    </div>
                </div>
                <div class="mt-4" id="device-status">
                    <div class="badge badge-primary badge-outline">Checking status...</div>
                </div>
            </div>
        </div>

        @if($device->type == 'actuator' || $device->type == 'ac')
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title">Control Mode</h2>
                    <div class="divider my-0"></div>

                    <div class="flex flex-col gap-4 mt-4">
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text text-lg font-medium">Automatic Control</span>
                                <div class="join">
                                    <span class="join-item btn btn-sm" id="mode-manual">MANUAL</span>
                                    <input type="checkbox" class="toggle toggle-primary toggle-lg join-item"
                                        id="auto-mode-toggle" />
                                    <span class="join-item btn btn-sm" id="mode-auto">AUTO</span>
                                </div>
                            </label>
                            <p class="text-sm mt-2 opacity-70">
                                When in AUTO mode, this device will be controlled automatically by the system based on
                                sensor readings.
                                In MANUAL mode, you can control the device directly.
                            </p>
                        </div>

                        <div class="alert alert-info" id="auto-mode-status">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Toggle the switch to change control mode</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($device->type == 'actuator')
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title">Actuator Control</h2>
                    <div class="divider my-0"></div>

                    <div class="flex flex-col gap-4 mt-4">
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text text-lg font-medium">Power</span>
                                <div class="join">
                                    <span class="join-item btn btn-sm" id="status-off">OFF</span>
                                    <input type="checkbox" class="toggle toggle-primary toggle-lg join-item"
                                        id="actuator-toggle" />
                                    <span class="join-item btn btn-sm" id="status-on">ON</span>
                                </div>
                            </label>
                        </div>

                        <div class="alert alert-info" id="control-status">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Toggle the switch to control the actuator</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <dialog id="actuator_confirm_modal" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg">Confirm Action</h3>
                    <p class="py-4">Are you sure you want to turn the actuator <span id="action-text"
                            class="font-bold"></span>?</p>
                    <div class="modal-action">
                        <button id="confirm-actuator-action" class="btn btn-primary">Confirm</button>
                        <button id="cancel-actuator-action" class="btn">Cancel</button>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        @endif

        @if($device->type == 'ac')
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title">AC Control</h2>
                    <div class="divider my-0"></div>

                    <div class="flex flex-col gap-4 mt-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-lg font-medium">Temperature</span>
                            </label>
                            <div class="join w-full flex flex-wrap">
                                <input type="radio" name="ac-temperature" value="off" class="join-item btn" id="ac-temp-off"
                                    checked />
                                <label for="ac-temp-off" class="join-item btn mr-4">OFF</label>

                                <input type="radio" name="ac-temperature" value="17" class="join-item btn"
                                    id="ac-temp-17" />
                                <label for="ac-temp-17" class="join-item btn mr-4">17°C</label>

                                <input type="radio" name="ac-temperature" value="20" class="join-item btn"
                                    id="ac-temp-20" />
                                <label for="ac-temp-20" class="join-item btn mr-4">20°C</label>

                                <input type="radio" name="ac-temperature" value="22" class="join-item btn"
                                    id="ac-temp-22" />
                                <label for="ac-temp-22" class="join-item btn mr-4">22°C</label>

                                <input type="radio" name="ac-temperature" value="25" class="join-item btn"
                                    id="ac-temp-25" />
                                <label for="ac-temp-25" class="join-item btn">25°C</label>
                            </div>
                        </div>

                        <div class="alert alert-info" id="ac-control-status">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Select a temperature setting to control the AC</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($device->type == 'sensor')
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title">Sensor Readings</h2>
                    <div class="divider my-0"></div>
                    <div class="h-80 w-full" id="chart-container">
                        <canvas id="sensorChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        @if($device->type == 'camera')
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Camera Feed</h2>
                    <div class="divider my-0"></div>
                    <div class="flex justify-center items-center my-4">
                        <div class="relative w-full max-w-3xl">
                            <div id="camera-feed-placeholder" class="bg-base-200 h-64 flex items-center justify-center">
                                <span class="loading loading-spinner loading-lg text-primary"></span>
                                <span class="ml-2">Loading camera feed...</span>
                            </div>
                            <img id="camera-feed" class="w-full rounded-lg shadow-lg hidden" alt="Camera Feed" />
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">History Data</h2>
                    <div class="divider my-0"></div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                <tr>
                                    <td colspan="2" class="text-center">Loading history data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-primary" id="load-more-btn">Load More</button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Store device information
        const deviceId = "{{ $device->deviceId }}";
        const deviceType = "{{ $device->type }}";

        // Variables for pagination
        let currentPage = 1;
        const pageSize = 30;
        let chart = null;

        // Variables for actuator control
        let actuatorState = false;
        let isControlUpdating = false;
        let acCurrentTemp = 'off'; // Variable to track AC temperature

        // Variables for auto mode toggle
        let autoModeState = {{ $device->auto_mode ? 'true' : 'false' }}; // Get from PHP
        let isAutoModeUpdating = false;

        // Chart.js configuration
        Chart.defaults.set('plugins.tooltip.callbacks.title', function (context) {
            return new Date(context[0].raw.x).toLocaleString();
        });

        // Helper function to format dates for chart labels

        // Fetch device history when the page loads
        document.addEventListener('DOMContentLoaded', function () {
            fetchDeviceStatus();

            // For camera type, we only need to update the feed
            if (deviceType === 'camera') {
                fetchCameraFeed();
            } else {
                fetchDeviceHistory();

                // Add event listener to load more button
                const loadMoreBtn = document.getElementById('load-more-btn');
                if (loadMoreBtn) {
                    loadMoreBtn.addEventListener('click', function () {
                        currentPage++;
                        fetchDeviceHistory(true);
                    });
                }
            }

            // Setup actuator toggle if it exists
            const actuatorToggle = document.getElementById('actuator-toggle');
            if (actuatorToggle) {
                actuatorToggle.addEventListener('change', function () {
                    // Get the current state of the toggle
                    const isOn = this.checked;

                    // Set the action text in the modal
                    document.getElementById('action-text').textContent = isOn ? 'ON' : 'OFF';

                    // Show the confirmation modal
                    const modal = document.getElementById('actuator_confirm_modal');
                    modal.showModal();

                    // Store the intended state for later use
                    modal.dataset.intendedState = isOn ? 'true' : 'false';

                    // Revert the checkbox to its previous state until the user confirms
                    this.checked = !isOn;
                });

                // Setup confirmation modal buttons
                document.getElementById('confirm-actuator-action').addEventListener('click', function () {
                    const modal = document.getElementById('actuator_confirm_modal');
                    const isOn = modal.dataset.intendedState === 'true';

                    // Update the toggle state to match the intended state
                    document.getElementById('actuator-toggle').checked = isOn;

                    // Close the modal
                    modal.close();

                    // Execute the toggle action
                    toggleActuator(isOn);
                });

                document.getElementById('cancel-actuator-action').addEventListener('click', function () {
                    // Just close the modal without making changes
                    document.getElementById('actuator_confirm_modal').close();
                });
            }

            // Setup auto mode toggle if it exists
            const autoModeToggle = document.getElementById('auto-mode-toggle');
            if (autoModeToggle) {
                // Set initial state
                autoModeToggle.checked = autoModeState;
                updateAutoModeStyles(autoModeState);

                autoModeToggle.addEventListener('change', function () {
                    const isAuto = this.checked;
                    toggleAutoMode(isAuto);
                });
            }

            // Setup AC temperature controls
            setupACTemperatureControls();
        });

        // Fetch current device status
        function fetchDeviceStatus() {
            fetch(`/api/payloads/${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    updateDeviceStatus(data);
                })
                .catch(error => {
                    console.error('Error fetching device status:', error);
                    document.getElementById('device-status').innerHTML =
                        '<div class="badge badge-error badge-outline">Error loading status</div>';
                });
        }

        // Update device status display
        function updateDeviceStatus(data) {
            const statusContainer = document.getElementById('device-status');

            if (!data || Object.keys(data).length === 0) {
                statusContainer.innerHTML = '<div class="badge badge-warning badge-outline">No data available</div>';
                return;
            }

            let statusHtml = '<div class="badge badge-success">Active</div>';
            statusHtml += '<div class="mt-2">';

            // Display data properties
            if (data.data) {
                for (const key in data.data) {
                    if (Object.hasOwnProperty.call(data.data, key)) {
                        const value = data.data[key];
                        statusHtml += `<p><span class="font-medium">${key}:</span> ${value}</p>`;

                        // Update actuator toggle if this is the status field and we're not currently updating
                        if (deviceType === 'actuator' && key === 'status' && !isControlUpdating) {
                            actuatorState = value === 'on';
                            const toggle = document.getElementById('actuator-toggle');
                            if (toggle) toggle.checked = actuatorState;

                            // Update button styles
                            updateToggleStyles(actuatorState);
                        }

                        // Update AC controls if this is temperature or status field and we're not currently updating
                        if (deviceType === 'ac' && !isControlUpdating) {
                            if (key === 'status') {
                                if (value === 'off') {
                                    acCurrentTemp = 'off';
                                    const tempRadio = document.getElementById('ac-temp-off');
                                    if (tempRadio) tempRadio.checked = true;
                                    updateACControlStyles('off');
                                }
                            } else if (key === 'value' && data.data.status === 'on') {
                                const tempValue = value.toString();
                                if (['17', '20', '22', '25'].includes(tempValue)) {
                                    acCurrentTemp = tempValue;
                                    const tempRadio = document.getElementById(`ac-temp-${tempValue}`);
                                    if (tempRadio) tempRadio.checked = true;
                                    updateACControlStyles(tempValue);
                                }
                            }
                        }

                        // Update camera feed if this is the url field for a camera
                        if (deviceType === 'camera' && key === 'url') {
                            updateCameraFeed(value);
                        }
                    }
                }
            }

            if (data.created_at) {
                const timestamp = new Date(data.created_at).toLocaleString();
                statusHtml += `<p class="text-sm mt-1 opacity-60">Last updated: ${timestamp}</p>`;
            }

            statusHtml += '</div>';
            statusContainer.innerHTML = statusHtml;
        }

        // Fetch camera feed data
        function fetchCameraFeed() {
            fetch(`/api/payloads/${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data[0].data && data[0].data.url) {
                        updateCameraFeed(data[0].data.url);
                    } else {
                        const feedPlaceholder = document.getElementById('camera-feed-placeholder');
                        if (feedPlaceholder) {
                            feedPlaceholder.innerHTML = '<div class="text-error">No camera feed URL available</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching camera feed:', error);
                    const feedPlaceholder = document.getElementById('camera-feed-placeholder');
                    if (feedPlaceholder) {
                        feedPlaceholder.innerHTML = '<div class="text-error">Error loading camera feed</div>';
                    }
                });
        }

        // Update camera feed with the given URL
        function updateCameraFeed(url) {
            if (!url) return;

            const cameraFeed = document.getElementById('camera-feed');
            const placeholder = document.getElementById('camera-feed-placeholder');

            if (cameraFeed && placeholder) {
                // Set the image source
                cameraFeed.src = url;

                // When image loads successfully, show it and hide placeholder
                cameraFeed.onload = function () {
                    cameraFeed.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };

                // If there's an error loading the image
                cameraFeed.onerror = function () {
                    cameraFeed.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    placeholder.innerHTML = '<div class="text-error">Failed to load camera feed</div>';
                };
            }
        }

        // Fetch device history data
        function fetchDeviceHistory(append = false) {
            const limit = pageSize * currentPage;
            fetch(`/api/payloads/${deviceId}/history/${limit}`)
                .then(response => response.json())
                .then(data => {
                    if (!append) {
                        updateHistoryTable(data);
                        if (deviceType === 'sensor') {
                            createOrUpdateChart(data);
                        }
                    } else {
                        appendToHistoryTable(data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching device history:', error);
                    document.getElementById('history-table-body').innerHTML =
                        '<tr><td colspan="2" class="text-center text-error">Error loading history data</td></tr>';
                });
        }

        // Update history table with data
        function updateHistoryTable(data) {
            const tableBody = document.getElementById('history-table-body');

            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2" class="text-center">No history data available</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            data.forEach(item => {
                appendHistoryRow(tableBody, item);
            });

            // Hide load more button if we've loaded all the data
            document.getElementById('load-more-btn').style.display = data.length < pageSize * currentPage ? 'none' : 'block';
        }

        // Append new data to history table
        function appendToHistoryTable(data) {
            const tableBody = document.getElementById('history-table-body');

            // Get the offset for the new data
            const offset = (currentPage - 1) * pageSize;

            // Add only the new items (avoiding duplicates)
            const newItems = data.slice(offset);
            newItems.forEach(item => {
                appendHistoryRow(tableBody, item);
            });

            // Hide load more button if we've loaded all the data
            document.getElementById('load-more-btn').style.display = data.length < pageSize * currentPage ? 'none' : 'block';
        }

        // Append a single row to the history table
        function appendHistoryRow(tableBody, item) {
            const row = document.createElement('tr');

            // Format the timestamp
            const timestamp = new Date(item.created_at).toLocaleString();

            // Format the data as a pretty JSON string
            let dataContent = '';
            if (item.data) {
                for (const key in item.data) {
                    if (Object.hasOwnProperty.call(item.data, key)) {
                        const value = item.data[key];
                        dataContent += `<div><span class="font-medium">${key}:</span> ${value}</div>`;
                    }
                }
            } else {
                dataContent = 'No data';
            }

            row.innerHTML = `
                <td>${timestamp}</td>
                <td>${dataContent}</td>
            `;
            tableBody.appendChild(row);
        }

        // Function to toggle actuator state
        function toggleActuator(isOn) {
            if (deviceType !== 'actuator') return;

            // Execute the toggle action directly since confirmation was already handled
            isControlUpdating = true;

            // Update UI to show that we're updating
            updateToggleStyles(isOn);
            const controlStatus = document.getElementById('control-status');
            controlStatus.className = 'alert alert-warning';
            controlStatus.innerHTML = `
                <span class="loading loading-spinner loading-sm"></span>
                <span>Updating actuator status...</span>
            `;

            // Send command to the server
            fetch(`/api/payloads/${deviceId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    data: {
                        status: isOn ? 'on' : 'off'
                    }
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update actuator');
                    }
                    return response.json();
                })
                .then(data => {
                    // Show success message
                    controlStatus.className = 'alert alert-success';
                    controlStatus.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Actuator ${isOn ? 'turned ON' : 'turned OFF'} successfully!</span>
                    `;
                })
                .catch(error => {
                    console.error('Error toggling actuator:', error);

                    // Show error message and revert toggle
                    controlStatus.className = 'alert alert-error';
                    controlStatus.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Failed to update actuator. Please try again.</span>
                    `;

                    // Revert the toggle to its previous state
                    const toggle = document.getElementById('actuator-toggle');
                    if (toggle) toggle.checked = actuatorState;
                    updateToggleStyles(actuatorState);
                })
                .finally(() => {
                    // Reset the updating flag after a short delay
                    setTimeout(() => {
                        isControlUpdating = false;
                    }, 1000);
                });
        }

        // Update the visual styles of the toggle buttons
        function updateToggleStyles(isOn) {
            const onButton = document.getElementById('status-on');
            const offButton = document.getElementById('status-off');

            if (onButton && offButton) {
                if (isOn) {
                    onButton.className = 'join-item btn btn-sm btn-success';
                    offButton.className = 'join-item btn btn-sm';
                } else {
                    offButton.className = 'join-item btn btn-sm btn-error';
                    onButton.className = 'join-item btn btn-sm';
                }
            }
        }

        // Create or update the chart with sensor data
        function createOrUpdateChart(data) {
            if (deviceType !== 'sensor' || !data || data.length === 0) return;

            const ctx = document.getElementById('sensorChart');

            // Hapus chart lama secara menyeluruh
            if (chart) {
                chart.destroy();
                chart = null;
            }

            // Filter data yang memiliki value
            const validData = data.filter(item => item.data?.value !== undefined);
            if (validData.length === 0) return;

            // Format data
            const chartData = validData.map(item => ({
                x: new Date(item.created_at),
                y: parseFloat(item.data.value)
            }));

            // Buat chart baru
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Value',
                        data: chartData,
                        borderColor: 'hsl(270, 70%, 60%)',
                        backgroundColor: 'hsl(270, 70%, 60%)33',
                        tension: 0.3,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        spanGaps: false, // Don't connect points across data gaps
                        showLine: true, // Ensure line is shown
                        borderJoinStyle: 'round' // Smooth line joins
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time', // <-- INI YANG PALING PENTING
                            time: {
                                unit: 'minute',
                                tooltipFormat: 'DD MMM YYYY HH:mm',
                                displayFormats: {
                                    minute: 'HH:mm'
                                }
                            }, ticks: {
                                autoSkip: true,
                                maxTicksLimit: 10,  // Control maximum number of ticks displayed
                                source: 'auto'      // Use 'data' to base ticks on your data points
                            },
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        },
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Value'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: (context) => {
                                    return new Date(context[0].raw.x).toLocaleString();
                                }
                            }
                        },
                        filler: {
                            propagate: false // Prevent fill propagation
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.3,
                            fill: false, // Again, ensure no fill
                            closed: false // Critical: prevent the line from closing
                        }
                    }
                }
            });
        }
        // Listen for real-time updates
        setTimeout(() => {
            window.Echo.channel(`device.${deviceId}`).listen('newHistoryEvent', (e) => {
                console.log(`Event received for device ${deviceId}:`, e);

                if (e.history && e.history.length > 0) {
                    // Update device status
                    const latestData = {
                        data: e.history[0].data,
                        created_at: e.history[0].created_at
                    };
                    updateDeviceStatus(latestData);

                    // Handle based on device type
                    if (deviceType === 'camera') {
                        // For camera, we only need to update the feed URL if it exists
                        if (e.history[0].data && e.history[0].data.url) {
                            updateCameraFeed(e.history[0].data.url);
                        }
                    } else if (deviceType !== 'camera') {
                        // For non-camera devices, update history table
                        const tableBody = document.getElementById('history-table-body');
                        if (tableBody) {
                            const noDataRow = tableBody.querySelector('tr td[colspan="2"]');

                            if (noDataRow) {
                                // Remove "no data" message if present
                                tableBody.innerHTML = '';
                            }

                            // Prepend the new data to the table
                            const tempElement = document.createElement('tbody');
                            appendHistoryRow(tempElement, e.history[0]);
                            tableBody.insertBefore(tempElement.firstChild, tableBody.firstChild);
                        }

                        // Update chart if this is a sensor
                        if (deviceType === 'sensor' && chart) {
                            // Tambahkan data baru
                            const newDataPoint = {
                                x: new Date(e.history[0].created_at),
                                y: e.history[0].data.value || 0
                            };

                            chart.data.datasets[0].data.push(newDataPoint);

                            // Ensure these settings are maintained when updating
                            chart.data.datasets[0].fill = false;
                            chart.options.elements.line.closed = false;

                            // Sort data chronologically to ensure correct line drawing
                            chart.data.datasets[0].data.sort((a, b) => a.x - b.x);

                            // Hapus data terlama jika melebihi 100 data point
                            if (chart.data.datasets[0].data.length > 100) {
                                chart.data.datasets[0].data.shift();
                            }

                            chart.update();
                        }
                    }
                }
            });
        }, 200);

        // Setup AC temperature controls
        function setupACTemperatureControls() {
            const acTempRadios = document.querySelectorAll('input[name="ac-temperature"]');
            const acControlStatus = document.getElementById('ac-control-status');

            acTempRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const tempValue = this.value;

                    // Update UI to show that we're updating
                    acControlStatus.className = 'alert alert-warning';
                    acControlStatus.innerHTML = `
                        <span class="loading loading-spinner loading-sm"></span>
                        <span>Updating AC temperature...</span>
                    `;

                    // Determine payload based on temperature
                    let status, value;
                    if (tempValue === 'off') {
                        status = 'off';
                        value = 0;
                    } else {
                        status = 'on';
                        value = parseInt(tempValue);
                    }

                    // Send command to the server
                    fetch(`/api/payloads/${deviceId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            data: {
                                status: status,
                                value: value
                            }
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to update AC temperature');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Show success message
                            acControlStatus.className = 'alert alert-success';
                            acControlStatus.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>AC ${tempValue === 'off' ? 'turned OFF' : 'set to ' + tempValue + '°C'} successfully!</span>
                        `;

                            // Update current temperature
                            acCurrentTemp = tempValue;
                        })
                        .catch(error => {
                            console.error('Error updating AC temperature:', error);

                            // Show error message
                            acControlStatus.className = 'alert alert-error';
                            acControlStatus.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Failed to update AC temperature. Please try again.</span>
                        `;

                            // Revert the radio button to its previous state
                            const prevRadio = document.getElementById(`ac-temp-${acCurrentTemp}`);
                            if (prevRadio) prevRadio.checked = true;
                        });
                });
            });
        }

        // Update the visual styles of the AC temperature controls
        function updateACControlStyles(selected) {
            const tempRadios = document.querySelectorAll('input[name="ac-temperature"]');
            tempRadios.forEach(radio => {
                const label = document.querySelector(`label[for="${radio.id}"]`);
                if (radio.value === selected) {
                    if (selected === 'off') {
                        label.className = 'join-item btn btn-error mr-4';
                    } else {
                        label.className = 'join-item btn btn-primary mr-4';
                    }
                } else {
                    label.className = 'join-item btn mr-4';
                }
            });
        }

        // Function to toggle auto mode
        function toggleAutoMode(isAuto) {
            if (isAutoModeUpdating) return;

            isAutoModeUpdating = true;

            // Update UI to show that we're updating
            updateAutoModeStyles(isAuto);
            const autoModeStatus = document.getElementById('auto-mode-status');
            autoModeStatus.className = 'alert alert-warning';
            autoModeStatus.innerHTML = `
                <span class="loading loading-spinner loading-sm"></span>
                <span>Updating control mode...</span>
            `;

            // Send command to the server
            fetch(`/api/devices/${deviceId}/auto-mode`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    auto_mode: isAuto
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update control mode');
                    }
                    return response.json();
                })
                .then(data => {
                    // Show success message
                    autoModeStatus.className = 'alert alert-success';
                    autoModeStatus.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Device set to ${isAuto ? 'AUTOMATIC' : 'MANUAL'} control mode successfully!</span>
                `;

                    // Update state
                    autoModeState = isAuto;
                })
                .catch(error => {
                    console.error('Error updating control mode:', error);

                    // Show error message and revert toggle
                    autoModeStatus.className = 'alert alert-error';
                    autoModeStatus.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Failed to update control mode. Please try again.</span>
                `;

                    // Revert the toggle to its previous state
                    const autoModeToggle = document.getElementById('auto-mode-toggle');
                    if (autoModeToggle) autoModeToggle.checked = autoModeState;
                    updateAutoModeStyles(autoModeState);
                })
                .finally(() => {
                    // Reset the updating flag after a short delay
                    setTimeout(() => {
                        isAutoModeUpdating = false;
                    }, 1000);
                });
        }

        // Update the visual styles of the auto mode toggle buttons
        function updateAutoModeStyles(isAuto) {
            const autoButton = document.getElementById('mode-auto');
            const manualButton = document.getElementById('mode-manual');

            if (autoButton && manualButton) {
                if (isAuto) {
                    autoButton.className = 'join-item btn btn-sm btn-success';
                    manualButton.className = 'join-item btn btn-sm';
                } else {
                    manualButton.className = 'join-item btn btn-sm btn-info';
                    autoButton.className = 'join-item btn btn-sm';
                }
            }
        }
    </script>
</body>

</html>