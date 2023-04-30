<?php
// Load required classes and functions
require_once('autoload.php');
require_once('Controllers/AuthController.php');
require_once('Controllers/TaskController.php');

// Define the authentication routes
$method = $_SERVER['REQUEST_METHOD'] ?? null;
$route = $_SERVER['REQUEST_URI'] ?? null;

$str = $route;
$prefix = "/techuplabs";

if (strpos($str, $prefix) === 0) { // Check if the string starts with the prefix
    $str = substr($str, strlen($prefix)); // Remove the prefix
}

$route = $str;

if (!$method || !$route) {
    apiResponse(404, "Invalid request");
}

if ($method === 'POST') {
    if ($route === '/register' || $route === '/login') {
        $authController = new AuthController();
        if ($route === '/register') {
            $authController->register($_POST);
        } else {
            $authController->login($_POST);
        }
    } elseif ($route === '/task_create') {
        $taskController = new TaskController();
        if ($route === '/task_create') {
            $taskController->create($_POST, $_FILES);
        }
    } else {
        apiResponse(404, "Invalid endpoints");
    }
} elseif ($method === 'GET') {
    if (strpos($route, '/task_list') === 0) {
        $taskController = new TaskController();
        $taskController->list($_GET);
    } else {
        apiResponse(404, "Invalid endpoints");
    }
} else {
    apiResponse(404, "Invalid endpoints");
}
