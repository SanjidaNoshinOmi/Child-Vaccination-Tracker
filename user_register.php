<?php
require_once('DBconnect.php');

$admin_secret = "MY_ADMIN_KEY_OMI123"; 

$selectedRole = isset($_POST['role']) ? $_POST['role'] : 'general';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $userid     = $_POST['userid'];
    $name       = $_POST['name'];
    $email      = $_POST['email'];
    $password   = $_POST['password'];
    $phone      = $_POST['phone'];
    $role       = $_POST['role'];
    $adminKey   = isset($_POST['adminkey']) ? $_POST['adminkey'] : "";

    if($role !== "admin" && $role !== "general"){
        die("❌ Invalid role selected.");
    }

    if($role === "admin" && $adminKey !== $admin_secret){
        echo "❌ Invalid Admin Key. You cannot register as admin.";
    } else {
        // Check if UserId already exists
        $checkSql = "SELECT 1 FROM users WHERE UserId = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $userid);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if(mysqli_num_rows($checkResult) > 0){
            echo "❌ User ID already exists. Please choose another.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (UserId, Name, Email, Password, Phone, Role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $userid, $name, $email, $hashed_password, $phone, $role);

            if(mysqli_stmt_execute($stmt)){
                echo "✅ Account created successfully. <a href='index.php'>Login here</a>";
            } else {
                echo "❌ Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Premium Registration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/u_register.css">
</head>

<body>
    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Create Account</h1>

        <form method="post" id="registrationForm">
            <div class="form-group">
                <i class="fas fa-id-card form-icon"></i>
                <input type="text" name="userid" placeholder="User ID" required 
                       value="<?php echo isset($_POST['userid']) ? htmlspecialchars($_POST['userid']) : ''; ?>" />
            </div>

            <div class="form-group">
                <i class="fas fa-user form-icon"></i>
                <input type="text" name="name" placeholder="Full Name" required 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />
            </div>

            <div class="form-group">
                <i class="fas fa-envelope form-icon"></i>
                <input type="email" name="email" placeholder="Email Address" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            </div>

            <div class="form-group">
                <i class="fas fa-lock form-icon"></i>
                <input type="password" name="password" placeholder="Password" required />
            </div>

            <div class="form-group">
                <i class="fas fa-phone form-icon"></i>
                <input type="text" name="phone" placeholder="Phone Number" required 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
            </div>

            <div class="role-section">
                <label for="role">
                    <i class="fas fa-user-cog"></i> Select Your Role:
                </label>
                <div class="form-group">
                    <i class="fas fa-users form-icon"></i>
                    <select name="role" id="role" onchange="this.form.submit()" required>
                        <option value="general" <?php if($selectedRole==='general') echo 'selected'; ?>>
                            🌟 General User
                        </option>
                        <option value="admin" <?php if($selectedRole==='admin') echo 'selected'; ?>>
                            👑 Administrator
                        </option>
                    </select>
                </div>

                <?php if($selectedRole === 'admin'): ?>
                    <div class="admin-key-group">
                        <div class="form-group">
                            <i class="fas fa-key form-icon"></i>
                            <input type="text" name="adminkey" placeholder="Enter Admin Security Key" required />
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-user-plus"></i> Create My Account
            </button>
        </form>
    </div>
</body>
</html>
