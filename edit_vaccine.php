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
$stmt = $conn->prepare("SELECT * FROM vaccine WHERE VaccineId = ?");
$stmt->bind_param("i", $vaccineId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Vaccine not found.");
}

$vaccine = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $age = $_POST['recommended_age'];
    $description = $_POST['description'];
    $aftereffects = $_POST['aftereffects'];

    $updateStmt = $conn->prepare("UPDATE vaccine SET Name=?, Type=?, Recommended_Age=?, Description=?, Aftereffects=? WHERE VaccineId=?");
    $updateStmt->bind_param("sssssi", $name, $type, $age, $description, $aftereffects, $vaccineId);

    if ($updateStmt->execute()) {
        header("Location: show_vaccines.php");
        exit();
    } else {
        echo "Update failed: " . $updateStmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vaccine - Medical Administration</title>
    <link rel="stylesheet" href="css/edit_vaccines.css">
<body>
    <div class="bg-element"></div>
    <div class="bg-element"></div>
    <div class="bg-element"></div>
    <div class="bg-element"></div>
    
    <div class="container" id="mainContainer">
        <h1>Edit Vaccine Information</h1>
        <form method="post" id="vaccineForm">
            <div class="form-group">
                <label for="name">Vaccine Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($vaccine['Name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="type">Vaccine Type</label>
                <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($vaccine['Type']); ?>">
            </div>
            
            <div class="form-group">
                <label for="recommended_age">Recommended Age</label>
                <input type="text" id="recommended_age" name="recommended_age" value="<?php echo htmlspecialchars($vaccine['Recommended_Age']); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($vaccine['Description']); ?>">
            </div>
            
            <div class="form-group">
                <label for="aftereffects">Side Effects</label>
                <input type="text" id="aftereffects" name="aftereffects" value="<?php echo htmlspecialchars($vaccine['Aftereffects']); ?>">
            </div>
            
            <div class="button-container">
                <button type="submit" id="updateBtn">
                    Update Vaccine
                </button>
                <a href="show_vaccines.php" class="back-link">
                    Back to List
                </a>
            </div>
        </form>
    </div>

    <script>
        // Enhanced form interactions
        document.getElementById('vaccineForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('updateBtn');
            const container = document.getElementById('mainContainer');
            
            btn.classList.add('loading');
            btn.textContent = 'Updating...';
            container.classList.add('form-submitted');
        });

        // Advanced input interactions
        document.querySelectorAll('input[type="text"]').forEach((input, index) => {
            input.addEventListener('focus', function() {
                this.closest('.form-group').style.transform = 'translateX(10px) scale(1.02)';
                this.closest('.form-group').style.zIndex = '10';
                
                // Add success indicator on valid input
                setTimeout(() => {
                    if (this.value.trim() && this.checkValidity()) {
                        if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('success-indicator')) {
                            const successIndicator = document.createElement('div');
                            successIndicator.classList.add('success-indicator');
                            successIndicator.innerHTML = '✓';
                            this.parentNode.style.position = 'relative';
                            this.parentNode.appendChild(successIndicator);
                        }
                    }
                }, 500);
            });

            input.addEventListener('blur', function() {
                this.closest('.form-group').style.transform = 'translateX(0) scale(1)';
                this.closest('.form-group').style.zIndex = '1';
            });

            // Real-time validation feedback
            input.addEventListener('input', function() {
                if (this.value.trim() && this.checkValidity()) {
                    this.style.borderColor = '#10b981';
                } else if (this.value.trim()) {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#e5e7eb';
                }
            });
        });

        // Parallax effect on mouse movement
        document.addEventListener('mousemove', function(e) {
            const bgElements = document.querySelectorAll('.bg-element');
            const x = (e.clientX / window.innerWidth) * 100;
            const y = (e.clientY / window.innerHeight) * 100;

            bgElements.forEach((element, index) => {
                const speed = (index + 1) * 0.8;
                element.style.transform = `translate(${x * speed * 0.01}px, ${y * speed * 0.01}px) rotate(${x * 0.1}deg)`;
            });
        });

        // Add typing sound effect simulation (visual feedback)
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('input', function() {
                this.style.transform = 'scale(1.005)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });

        // Page visibility effects
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                document.querySelector('.container').style.filter = 'blur(2px)';
            } else {
                document.querySelector('.container').style.filter = 'blur(0px)';
            }
        });
    </script>
</body>
</html>
