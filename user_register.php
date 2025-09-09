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
<html>
<head>
    <title>Create Account</title>
</head>
<body>
  <h1>Create New Account</h1>
  <form method="post">
    <input type="text" name="userid" placeholder="User ID" required value="<?php echo isset($_POST['userid']) ? htmlspecialchars($_POST['userid']) : ''; ?>" /><br>
    <input type="text" name="name" placeholder="Full Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" /><br>
    <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" /><br>
    <input type="password" name="password" placeholder="Password" required /><br>
    <input type="text" name="phone" placeholder="Phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" /><br>

    <label for="role">Select Role:</label>
    <select name="role" id="role" onchange="this.form.submit()" required>
      <option value="general" <?php if($selectedRole==='general') echo 'selected'; ?>>General User</option>
      <option value="admin" <?php if($selectedRole==='admin') echo 'selected'; ?>>Admin</option>
    </select><br>

    <?php if($selectedRole === 'admin'): ?>
        <input type="text" name="adminkey" placeholder="Enter Admin Key" required /><br>
    <?php endif; ?>

    <input type="submit" value="Register" />
  </form>
</body>
</html>

