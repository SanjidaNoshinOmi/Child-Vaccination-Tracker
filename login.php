<?php
session_start();
require_once('DBconnect.php');

if (isset($_POST['userid']) && isset($_POST['password'])) {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    $sql = "SELECT UserId, Name, Role, Password FROM users WHERE UserId = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $stored_hashed_password = $user['Password'];

        if (password_verify($password, $stored_hashed_password)) {
            $_SESSION['UserId'] = $user['UserId'];
            $_SESSION['Name'] = $user['Name'];
            $_SESSION['Role'] = $user['Role'];

            if ($user['Role'] === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: general_dashboard.php");
            }
            exit;
        } else {
            // pw was incorrect.
            echo "❌ Wrong UserID or Password";
        }
    } else {
        // No user found with that UserID.
        echo "❌ Wrong UserID or Password";
    }
}
?>
