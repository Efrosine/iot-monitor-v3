<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </div>

    <script>
        // Store device information
        const deviceId = "{{ $device->deviceId }}";
        const deviceType = "{{ $device->type }}";

        // Variables for pagination
        let currentPage = 1;
        const pageSize = 10;
        let chart = null;

        // Chart.js configuration
        Chart.defaults.set('plugins.tooltip.callbacks.title', function (context) {
            return new Date(context[0].raw.x).toLocaleString();
        });

        // Helper function to format dates for chart labels

        // Fetch device history when the page loads
        document.addEventListener('DOMContentLoaded', function () {
            fetchDeviceStatus();
            fetchDeviceHistory();

            // Add event listener to load more button
            document.getElementById('load-more-btn').addEventListener('click', function () {
                currentPage++;
                fetchDeviceHistory(true);
            });
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

                    // Add new entry to top of history table
                    const tableBody = document.getElementById('history-table-body');
                    const noDataRow = tableBody.querySelector('tr td[colspan="2"]');

                    if (noDataRow) {
                        // Remove "no data" message if present
                        tableBody.innerHTML = '';
                    }

                    // Prepend the new data to the table
                    const tempElement = document.createElement('tbody');
                    appendHistoryRow(tempElement, e.history[0]);
                    tableBody.insertBefore(tempElement.firstChild, tableBody.firstChild);

                    // Update chart if this is a sensor
                    // Update bagian ini dalam event listener
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
            });
        }, 200);
    </script>
</body>

</html>