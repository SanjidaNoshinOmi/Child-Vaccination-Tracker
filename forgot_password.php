<?php
session_start();
include 'DBconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = mysqli_real_escape_string($conn, $_POST['userid']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);

    $sql = "SELECT * FROM Users WHERE UserId='$userid' AND Email='$email'";
    $result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) === 1) {
    $_SESSION['reset_userid'] = $userid;
    header("Location: reset_password.php");
    exit;
}

    
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
	<link rel="stylesheet" href="css/forgetPW.css">  <!-- external CSS file -->
    <title>Reset Password - Child Vaccination Tracker</title>

</head>
<body>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="container">
        <div class="reset_box">
            <div class="key-icon"></div>
            <h1>Reset Password</h1>
            <p class="subtitle">Enter your User ID and registered email to reset your password</p>
            
            <form method="post">
                <div class="input-group">
                    <input type="text" name="userid" placeholder="Enter your User ID" required />
                </div>
                
                <div class="input-group">
                    <input type="email" name="email" placeholder="Enter your registered Email" required />
                </div>
                
                <div class="input-group">
                    <input type="submit" value="Send Reset Link" />
                </div>
            </form>
            
            <div class="back-link">
                <a href="index.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>

