<?php
session_start();
include 'DBconnect.php';

if (!isset($_SESSION['UserId'])) {
    die("Please log in first.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId   = $_SESSION['UserId'];
    $childId  = trim($_POST['childId']);
    $name     = trim($_POST['name']);
    $dob      = trim($_POST['dob']);
    $gender   = trim($_POST['gender']);
    $blood    = trim($_POST['blood']);
    $father   = trim($_POST['father']);
    $mother   = trim($_POST['mother']);
    $guardian = trim($_POST['guardian']);
	
    if (empty($dob)) {
        die("❌ Date of Birth is required.");
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $dob) {
        die("❌ Invalid Date of Birth. Please use YYYY-MM-DD format.");
    }

    $sql = "INSERT INTO child_profiles 
            (UserId, ChildId, Name, DateOfBirth, Gender, BloodGroup, FatherName, MotherName, GuardianName)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisssssss", $userId, $childId, $name, $dob, $gender, $blood, $father, $mother, $guardian);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green;'>✅ Child registered successfully!</p>";
    
	

    } else {
        echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
	<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap"
      rel="stylesheet"
    />
	<link rel="stylesheet" href="css/register_ChildProfiles.css">  <!-- external CSS file -->
    <title>Register Child - Child Vaccination Tracker</title>

</head>
<body>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <div class="container">
        <div class="register_box">
            <h1>Register Child Profile</h1>
            <p class="subtitle">Create a comprehensive profile for your child's vaccination tracking</p>
            <form method="post">
                <div class="form-row">
                    <div class="form-group child-id-field">
                        <label for="childId">Child ID</label>
                        <input type="number" name="childId" id="childId" required>
                    </div>
                    
                    <div class="form-group name-field">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group dob-field">
                        <label for="dob">Date of Birth</label>
                        <input type="date" name="dob" id="dob" required>
                    </div>
                    
                    <div class="form-group gender-field">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group blood-field">
                        <label for="blood">Blood Group</label>
                        <input type="text" name="blood" id="blood" placeholder="e.g., A+, B-, O+">
                    </div>
                    
                    <div class="form-group father-field">
                        <label for="father">Father's Name</label>
                        <input type="text" name="father" id="father">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group mother-field">
                        <label for="mother">Mother's Name</label>
                        <input type="text" name="mother" id="mother">
                    </div>
                    
                    <div class="form-group guardian-field">
                        <label for="guardian">Guardian's Name</label>
                        <input type="text" name="guardian" id="guardian">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit">Register Child</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>