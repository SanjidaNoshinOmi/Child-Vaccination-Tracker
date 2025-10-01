<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Error: Center ID not provided.");
}

$centerId = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM centers WHERE CenterId = ?");
$stmt->bind_param("i", $centerId);

if ($stmt->execute()) {
    header("Location: show_centers.php");
    exit();
} else {
    echo "Deletion Failed: " . $stmt->error;
}

$stmt->close();
?>
