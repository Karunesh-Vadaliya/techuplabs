<?php
// Define the excluded endpoints
$excluded_endpoints = array('/login', '/register');

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Check if the current request is excluded
$exclude_request = false;
foreach ($excluded_endpoints as $endpoint) {
    if (strpos($request_uri, $endpoint) !== false) {
        $exclude_request = true;
        break;
    }
}

// If the current request is not excluded, verify the JWT token
if (!$exclude_request) {
    // Get the token from the Authorization header
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    // If the token is missing, return an error response
    if (!$token) {
        apiResponse(400, "Missing token");
    }

    // Verify the token and get the user ID
    try {
        $jwt = new JwtHandler();

        $decoded_token = $jwt->jwtDecodeData($token);
        $user_id = $decoded_token->data->id;
    } catch (Exception $e) {
        apiResponse(400, 'Invalid token');
    }

    // Set the user ID as a global variable for use in the rest of the application
    $GLOBALS['user_id'] = $user_id;
}
