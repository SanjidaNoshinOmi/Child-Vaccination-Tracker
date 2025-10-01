<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Error: Vaccine ID not provided.");
}

$vaccineId = intval($_GET['id']); 

$stmt = $conn->prepare("DELETE FROM vaccine WHERE VaccineId = ?");
$stmt->bind_param("i", $vaccineId);

if ($stmt->execute()) {
    header("Location: show_vaccines.php");
    exit();
} else {
    echo "Error deleting vaccine: " . $stmt->error;
}
?>
