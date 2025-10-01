<?php
session_start();
include 'DBconnect.php';
if (isset($_GET['userid'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['userid']);
    $_SESSION['reset_userid'] = $uid;
}

if (isset($_SESSION['reset_userid'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPass = mysqli_real_escape_string($conn, $_POST['new_password']);
        $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);

        $uid = $_SESSION['reset_userid'];
        $update = "UPDATE Users SET Password='$hashedPass' WHERE UserId='$uid'";
        mysqli_query($conn, $update);

        unset($_SESSION['reset_userid']); // clear session
        echo "✅ Password reset successful. <a href='index.php'>Login</a>";
        exit;
    }
} else {
    echo "❌ No user ID found for reset request.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap"
      rel="stylesheet"
    />
	<link rel="stylesheet" href="css/resetPW.css">  <!-- external CSS file -->
    <title>Set New Password - Child Vaccination Tracker</title>

<body>
    <h1>Choose a New Password</h1>
    <form method="post">
        <input type="password" name="new_password" placeholder="Enter new password" required /><br>
        <input type="submit" value="Reset Password" />
    </form>
</body>
</html>