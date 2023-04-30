<?php
require_once('autoload.php');

$responseFlag = false;
$responseMessage = 'Something went wrong..!';

// Define array of queries to execute
$queries = array("INSERT INTO users (name, email, password) VALUES ('Admin User', 'admin@techuplabs.com','" . md5('TechUp@2023') . "')");

try {
    // Connect to the database
    $dbConnection = new Database();
    foreach ($queries as $query) {
        $dbConnection->query($query);
    }
    $responseMessage =  "Seeder successful!";
    $responseFlag = true;
} catch (PDOException $e) {
    $responseMessage = "Seeder failed: " . $e->getMessage();
}
apiResponse($responseFlag, $responseMessage);
