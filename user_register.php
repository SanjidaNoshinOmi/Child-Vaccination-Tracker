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

    <div class="floating-element">
        <i class="fas fa-user-plus" style="font-size: 3rem; color: rgba(255,255,255,0.1);"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-shield-alt" style="font-size: 2.5rem; color: rgba(255,255,255,0.1);"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-key" style="font-size: 2rem; color: rgba(255,255,255,0.1);"></i>
    </div>

    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Create Account</h1>
        
        <?php if($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="message">
                <?php
                if($role === "admin" && $adminKey !== $admin_secret){
                    echo "❌ Invalid Admin Key. You cannot register as admin.";
                } else {
                    if(mysqli_stmt_execute($stmt)){
                        echo "✅ Account created successfully. <a href='index.php'>Login here</a>";
                    } else {
                        echo "❌ Error: " . mysqli_error($conn);
                    }
                }
                ?>
            </div>
        <?php endif; ?>
        
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

            <button type="submit" class="submit-btn" onclick="this.classList.add('loading')">
                <i class="fas fa-user-plus"></i> Create My Account
            </button>
        </form>
    </div>

    <script>
        // Add smooth interactions
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Add typing animation to placeholders
            const placeholders = [
                'Type your unique User ID...',
                'Enter your full name...',
                'Your email address...',
                'Create a secure password...',
                'Your contact number...'
            ];

            // Enhanced form validation feedback
            const form = document.getElementById('registrationForm');
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.submit-btn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                
                // Add subtle animation to form during submission
                this.style.transform = 'scale(0.98)';
                this.style.opacity = '0.8';
            });
        });
    </script>
</body>
</html>
