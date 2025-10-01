<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    header("Location: index.php");
    exit();
}

// Get child info from child_profiles
$childId = isset($_GET['childId']) ? intval($_GET['childId']) : 0;
if ($childId === 0) die("Child ID missing.");
$sql = "SELECT Name, FatherName, MotherName, GuardianName FROM child_profiles WHERE ChildId=? AND UserId=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $childId, $_SESSION['UserId']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("Child not found or does not belong to you.");
}

$child = mysqli_fetch_assoc($result);

// Handl
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guardianContact = $_POST['guardianContact'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $vaccinatedStatus = $_POST['vaccinatedStatus'];
    $allergies = $_POST['allergies'];
    $chronicIllness = $_POST['chronicIllness'];
    $medications = $_POST['medications'];
    $notes = $_POST['notes'];
    $sql_insert = "INSERT INTO healthcard
        (ChildId, GuardianContact, Weight, Height, VaccinatedStatus, Allergies, ChronicIllness, Medications, Notes, DownloadDate)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql_insert);
    
    mysqli_stmt_bind_param($stmt, "isddsssss",
        $childId, $guardianContact, $weight, $height, $vaccinatedStatus, $allergies, $chronicIllness, $medications, $notes
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: healthcard.php?childId=" . $childId);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Healthcard for <?= htmlspecialchars($child['Name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/add_healthcard.css">
</head>
<body>
    <div class="page-loader">
        <div class="loader-content">
            <div class="spinner"></div>
            <p>Loading Health Card Form...</p>
        </div>
    </div>

    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="general_dashboard.php">Child Vaccination Tracker</a></h1>
            </div>
            <ul class="nav_link">
                <li><a href="register_ChildProfiles.php">Register Child</a></li>
                <li><a href="childProfiles.php">Child Profiles</a></li>
                <li><a href="payment.php">Payment</a></li>
                <li><a href="show_vaccines.php">Vaccines</a></li>
                <li><a href="show_centers.php">Centers</a></li>
                <li><a href="childVaccination.php">Child Vaccination</a></li>
                <li><a href="logout.php" onclick="return confirm('Log out of your account?');">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="main-container">
        <h1>🩺 Add Health Card</h1>
        <h3>for <?= htmlspecialchars($child['Name']) ?></h3>

        <form method="post">
            <input type="hidden" name="childId" value="<?= htmlspecialchars($childId) ?>">

            <div class="form-group">
                <p><strong>Father Name:</strong> <?= htmlspecialchars($child['FatherName']) ?><br>
                <strong>Mother Name:</strong> <?= htmlspecialchars($child['MotherName']) ?><br>
                <strong>Guardian Name:</strong> <?= htmlspecialchars($child['GuardianName']) ?></p>
            </div>

            <div class="form-group">
                <label for="guardianContact">Guardian Contact:</label>
                <input type="text" id="guardianContact" name="guardianContact" required>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg):</label>
                <input type="text" id="weight" name="weight">
            </div>

            <div class="form-group">
                <label for="height">Height (cm):</label>
                <input type="text" id="height" name="height">
            </div>

            <div class="form-group">
                <label for="vaccinatedStatus">Vaccination Status:</label>
                <input type="text" id="vaccinatedStatus" name="vaccinatedStatus">
            </div>

            <div class="form-group">
                <label for="allergies">Allergies:</label>
                <textarea id="allergies" name="allergies" placeholder="List any known allergies..."></textarea>
            </div>

            <div class="form-group">
                <label for="chronicIllness">Chronic Illness:</label>
                <textarea id="chronicIllness" name="chronicIllness" placeholder="List any chronic conditions..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="medications">Medications:</label>
                <textarea id="medications" name="medications" placeholder="List current medications..."></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" placeholder="Additional medical notes or observations..."></textarea>
            </div>

            <button type="submit" class="btn-submit">💾 Save Health Card</button>
        </form>
        
        <div class="back-link">
            <a href="childProfiles.php">⬅ Back to Child Profiles</a>
        </div>
    </div>

    <script>
        // Add smooth focus transitions
        document.querySelectorAll('input, textarea').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(5px)';
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });

        // Add form validation feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredField = document.getElementById('guardianContact');
            if (!requiredField.value.trim()) {
                e.preventDefault();
                requiredField.focus();
                requiredField.style.borderColor = '#e74c3c';
                requiredField.style.boxShadow = '0 0 0 4px rgba(231, 76, 60, 0.1)';
                
                setTimeout(() => {
                    requiredField.style.borderColor = '';
                    requiredField.style.boxShadow = '';
                }, 2000);
            }
        });

        // Add typing animation for textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.transform = 'scale(1.01)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });

        // Parallax effect for floating shapes
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.floating-shape');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;

            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                const x = mouseX * speed * 50;
                const y = mouseY * speed * 50;
                shape.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
    </script>
</body>
</html>