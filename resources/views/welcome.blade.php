<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    @vite(['resources/js/app.js'])



</body>
<script>
    setTimeout(() => {
        // Listen to main history channel for all devices
        window.Echo.channel('history').listen('newHistoryEvent', (e) => {
            console.log('Global history event:', e);
        });

        // Example: Listen to a specific device channel
        // Replace 'device-id-here' with the actual device ID or dynamically set it
        window.Echo.channel('device.test-Dev020').listen('newHistoryEvent', (e) => {
            console.log('Device-specific event:', e);
        });
    }, 200);
</script>

</html>