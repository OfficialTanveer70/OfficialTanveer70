<?php
$servername = "fdb1029.awardspace.net"; // Aksar 'localhost' hi hota hai, agar host alag ho to wahan se dekh lein
$username = "4768901_tanveer"; 
$password = "##T4nv333r##";
$dbname = "4768901_tanveer";

// Connection banayein
$conn = new mysqli($servername, $username, $password, $dbname);

// Connection check karein
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>