<?php

require_once __DIR__ . '/../config/database.php';

$database = new Database();

$conn = $database->connect();

echo "<h1>Database Connection Successful!</h1>";
echo "<p>Online Car Spare Parts Store is connected to MySQL.</p>";

?>