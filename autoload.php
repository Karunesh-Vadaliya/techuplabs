<?php
// Allow CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once('config.php');
require_once('classes/Database.php');
require_once('classes/JwtHandler.php');
require_once('Miidleware/authMiddleware.php');

// Function to return API responses
function apiResponse($code, $message, $data = null)
{
  $status = $code == 200;
  
  $response = array(
    "status" => $status,
    "message" => $message,
    "data" => $data
  );

  http_response_code($code);
  header("Content-Type: application/json");
  echo json_encode($response);
  exit;
}
