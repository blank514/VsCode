<?php
session_start();
echo "ID : " . ($_SESSION['user_id'] ?? "Non connecté")."<br>";
echo "full_name : " . ($_SESSION['full_name'] ?? "Non connecté")."<br>";
echo "inspector_id : " . ($_SESSION['inspector_id'] ?? "Non connecté")."<br>";
echo "email : " . ($_SESSION['email'] ?? "Non connecté")."<br>";
echo "phone_number : " . ($_SESSION['phone_number'] ?? "Non connecté")."<br>";


// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Change if you have a password
$dbname = "quality_process";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_db) === TRUE) {
    echo "✅ Database '$dbname' created or already exists.<br>";
} else {
    echo "❌ Error creating database: " . $conn->error . "<br>";
}

?>