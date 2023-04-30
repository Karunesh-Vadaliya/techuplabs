<?php
require_once('autoload.php');

$responseFlag = false;
$responseMessage = 'Something went wrong..!';

// Define array of queries to execute
$queries = array(
    "CREATE TABLE IF NOT EXISTS users (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS tasks (
      id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      user_id BIGINT(20) UNSIGNED NOT NULL,
      subject VARCHAR(255) NOT NULL,
      description TEXT,
      start_date DATE,
      due_date DATE,
      status ENUM('New', 'Incomplete', 'Complete') DEFAULT 'New',
      priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS notes (
      id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      task_id BIGINT(20) UNSIGNED NOT NULL,
      subject VARCHAR(255) NOT NULL,
      note TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS attachments (
      id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      note_id BIGINT(20) UNSIGNED NOT NULL,
      filename VARCHAR(255) NOT NULL,
      filepath VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
    )"
);

try {
    // Connect to the database
    $dbConnection = new Database();
    foreach ($queries as $query) {
        $dbConnection->query($query);
    }
    $responseMessage =  "Migration successful!";
    $responseFlag = true;
} catch (PDOException $e) {
    $responseMessage = "Migration failed: " . $e->getMessage();
}
apiResponse($responseFlag, $responseMessage);
