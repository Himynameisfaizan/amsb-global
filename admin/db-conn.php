<?php
if (session_status() === PHP_SESSION_NONE) {
    // session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Database Configuration
$local = false; 

if ($local) {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbName = 'amsb_global';
    $site = "http://localhost/projects/amsb-global/";
} else {
    $host = 'localhost';
    $username = 'u799879276_amsb_global';
    $password = 'Amsb@global1';
    $dbName = 'u799879276_amsb_global';
    $site = 'https://wandering-honeydew-hippopotamus-07.hostingersite.com/';
}

// Make `$site` global
global $site;

// Create Database Connection
$conn = new mysqli($host, $username, $password, $dbName);

// Check Connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional: Set Character Encoding to UTF-8
$conn->set_charset("utf8");

?>