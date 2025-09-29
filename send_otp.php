<?php
session_start();

// DB connection
$host = "localhost";
$user = "root";      // change if different
$pass = "";          // change if different
$db   = "cada_db";   // change to your DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Collect form data
$surname = $_POST['surname'];
$other_names = $_POST['other_names'];
$national_id_number = $_POST['national_id_number'];
$dob = $_POST['dob'];
$sex = $_POST['sex'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$county = $_POST['county'];
$constituency = $_POST['constituency'];
$ward = $_POST['ward'];
$consent = isset($_POST['consent']) ? 1 : 0;

// Save to DB
$sql = "INSERT INTO members (surname, other_names, national_id_number, dob, sex, email, phone, county, constituency, ward, consent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssi", $surname, $other_names, $national_id_number, $dob, $sex, $email, $phone, $county, $constituency, $ward, $consent);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
