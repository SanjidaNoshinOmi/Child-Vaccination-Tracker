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

$stmt = $conn->prepare("SELECT * FROM centers WHERE CenterId = ?");
$stmt->bind_param("i", $centerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Center not found.");
}

$center = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $availableVaccines = $_POST['available_vaccines'];

    $updateStmt = $conn->prepare("UPDATE centers SET Name = ?, Address = ?, Contact = ?, AvailableVaccines = ? WHERE CenterId = ?");
    $updateStmt->bind_param("ssssi", $name, $address, $contact, $availableVaccines, $centerId);

    if ($updateStmt->execute()) {
        header("Location: show_centers.php");
        exit();
    } else {
        echo "Update Failed: " . $updateStmt->error;
    }

    $updateStmt->close();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Center - Vaccination Management</title>
	<link rel="stylesheet" href="css/edit_center.css">
</head>
<body>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    
    <div class="container">
        <h1>✨ Edit Vaccination Center</h1>
        <form method="post" id="editForm">
            <div class="form-group">
                <label for="name">🏥 Center Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($center['Name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">📍 Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($center['Address']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contact">📞 Contact Information</label>
                <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($center['Contact']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="available_vaccines">💉 Available Vaccines</label>
                <input type="text" id="available_vaccines" name="available_vaccines" value="<?php echo htmlspecialchars($center['AvailableVaccines']); ?>">
            </div>
            
            <div class="button-container">
                <button type="submit" id="updateBtn">
                    Update Center
                </button>
                <a href="show_centers.php" class="cancel-link">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Add loading state to button on form submission
        document.getElementById('editForm').addEventListener('submit', function() {
            const btn = document.getElementById('updateBtn');
            btn.classList.add('loading');
            btn.textContent = 'Updating...';
        });

        // Add input interaction effects
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-group').style.transform = 'scale(1.02)';
                this.closest('.form-group').querySelector('label').style.transform = 'translateY(0)';
                this.closest('.form-group').querySelector('label').style.color = '#667eea';
            });

            input.addEventListener('blur', function() {
                this.closest('.form-group').style.transform = 'scale(1)';
                this.closest('.form-group').querySelector('label').style.color = '#4a5568';
            });
        });

        // Add subtle parallax effect on mouse move
        document.addEventListener('mousemove', function(e) {
            const particles = document.querySelectorAll('.particle');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;

            particles.forEach((particle, index) => {
                const speed = (index + 1) * 0.5;
                particle.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    </script>
</body>
</html>