<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Test</title>
</head>
<body>
    <h1>Booking System Test</h1>

    <div id="result"></div>

    <button onclick="testBooking()">Test Booking API</button>

    <script>
        async function testBooking() {
            const result = document.getElementById('result');
            result.innerHTML = 'Testing booking API...';

            try {
                const response = await fetch('/api/reservations/{{ Auth::id() }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        station_id: 13, // Nexl tech station
                        start_time: new Date(Date.now() + 30 * 60 * 1000).toISOString(),
                        end_time: new Date(Date.now() + 150 * 60 * 1000).toISOString(),
                        duration_hours: 2,
                        total_amount: 50.00
                    })
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);

                const data = await response.json();
                console.log('Response data:', data);

                if (response.ok) {
                    result.innerHTML = `
                        <h2 style="color: green;">✅ SUCCESS</h2>
                        <p>Booking created successfully!</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    result.innerHTML = `
                        <h2 style="color: red;">❌ ERROR</h2>
                        <p>Status: ${response.status}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }

            } catch (error) {
                console.error('Fetch error:', error);
                result.innerHTML = `
                    <h2 style="color: red;">❌ NETWORK ERROR</h2>
                    <p>${error.message}</p>
                    <p>Check browser console for details</p>
                `;
            }
        }

        // Test GET request too
        async function testGet() {
            try {
                const response = await fetch('/api/reservations/{{ Auth::id() }}');
                const data = await response.json();
                console.log('GET reservations:', data);
            } catch (error) {
                console.error('GET error:', error);
            }
        }

        // Run tests on page load
        testGet();
    </script>
</body>
</html>
