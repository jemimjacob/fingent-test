<?php

// Array to store request counts per referrer
$requestCounts = [];

// Configurable maximum requests per second
$maxRequestsPerSecond = 2;

function serveResource($referrer) {
    global $requestCounts, $maxRequestsPerSecond;

    if ($referrer) {
        // Retrieve the request count for the referrer
        $requestCount = $requestCounts[$referrer] ?? 0;

        // Check if the request count exceeds the maximum limit
        if ($requestCount >= $maxRequestsPerSecond) {
            http_response_code(429);
            $result = json_encode(['error' => 'Rate limit exceeded']);
        } else {
            $result = [];
        }

        // Update the request count for the referrer
        $requestCounts[$referrer] = $requestCount + 1;
    }

    return $result;
}

// Number of requests to send
$numRequests = 5;

// Delay between requests (in microseconds)
$delayBetweenRequests = 1000000 / $maxRequestsPerSecond;

// API endpoint URL
echo 'Demo API url: https://reqres.in/api/users/2 <br>';
$apiUrl = 'https://reqres.in/api/users/2';

// Referrer header value
$referrer = 'https://example.com';

// Create a cURL handle
$ch = curl_init();

// Set common cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Referer: ' . $referrer]);

// Send multiple requests
for ($i = 0; $i < $numRequests; $i++) {
    // Throttle the API calls
    $result = serveResource($referrer);

    if (empty($result)) {
        // Execute the cURL request
        $response = curl_exec($ch);

        // Print the response
        echo "<br><br>Response $i: $response\n";
    }

    // Delay between requests
    usleep($delayBetweenRequests);
}

// Close the cURL handle
curl_close($ch);
