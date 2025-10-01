<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    die("Access denied.");
}

if (!isset($_GET['recordId'])) {
    die("No record selected.");
}

$recordId = intval($_GET['recordId']);

// Initialize variables to prevent undefined variable warnings
$showSuccess = false;
$errorMessage = "";

// Fetch record
$sql = "SELECT cv.*, v.Name AS VaccineName
        FROM childvaccination cv
        JOIN vaccine v ON cv.VaccineId = v.VaccineId
        WHERE cv.RecordId=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $recordId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$record = mysqli_fetch_assoc($res);

if (!$record) {
    die("Invalid record.");
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $actualDate = !empty($_POST['actual_date']) ? $_POST['actual_date'] : NULL;
    $centerId = intval($_POST['center_id']);
    $status = "Completed";

    $update = "UPDATE childvaccination SET ActualDate=?, CenterId=?, Status=? WHERE RecordId=?";
    $stmt2 = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt2, "sisi", $actualDate, $centerId, $status, $recordId);

    if (mysqli_stmt_execute($stmt2)) {
        $showSuccess = true; // Set success flag to true
    } else {
        $errorMessage = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vaccine Record - Child Health Management</title>
    <link rel="stylesheet" href="css/edit_vaccineRecord.css">
</head>
<body>
    <div class="medical-element">💉</div>
    <div class="medical-element">🩺</div>
    <div class="medical-element">🏥</div>
    <div class="medical-element">👶</div>
    <div class="medical-element">📋</div>
    <div class="medical-element">✅</div>
    
    <div class="container">
        <div class="title-section">
            <h1>Edit Vaccine Record</h1>
        </div>

        <div class="record-info">
            <div style="font-size: 1.5rem; margin-bottom: 10px;">💉 <?php echo htmlspecialchars($record['VaccineName']); ?></div>
            <div>📊 Dose Number: <strong><?php echo htmlspecialchars($record['Dose_Number']); ?></strong></div>
        </div>

        <?php if ($showSuccess): ?>
        <div class="success-message">
            ✅ Record updated successfully! The vaccination information has been saved.
        </div>
        <div style="text-align: center;">
            <a href="show_childVaccination.php?childId=<?php echo $record['ChildId']; ?>" class="back-link">
                ← Back to Records
            </a>
        </div>
        <?php else: ?>
        
        <div class="form-container">
            <form method="post" id="vaccineForm">
                <div class="form-group">
                    <label for="actual_date">Actual Vaccination Date</label>
                    <input type="date" id="actual_date" name="actual_date" value="<?php echo htmlspecialchars($record['ActualDate']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="center_id">Vaccination Center</label>
                    <select name="center_id" id="center_id">
                        <?php
                        $cRes = mysqli_query($conn, "SELECT CenterId, Name FROM centers");
                        while ($c = mysqli_fetch_assoc($cRes)) {
                            $sel = ($c['CenterId'] == $record['CenterId']) ? "selected" : "";
                            echo "<option value='{$c['CenterId']}' $sel>".htmlspecialchars($c['Name'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="button-container">
                    <button type="submit" id="saveBtn" class="medical-pulse">
                        💾 Save Changes
                    </button>
                    <a href="show_childVaccination.php?childId=<?php echo $record['ChildId']; ?>" class="back-link">
                        ← Back to Records
                    </a>
                </div>
            </form>
        </div>
        
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
        <div style="color: #e53e3e; background: rgba(229, 62, 62, 0.1); padding: 20px; border-radius: 12px; margin-top: 20px; text-align: center; font-weight: 600;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Enhanced form interactions
        document.getElementById('vaccineForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('saveBtn');
            btn.classList.add('loading');
            btn.innerHTML = '💾 Saving...';
        });

        // Advanced field interactions
        document.querySelectorAll('input, select').forEach((field, index) => {
            field.addEventListener('focus', function() {
                this.closest('.form-group').style.transform = 'translateX(12px) scale(1.02)';
                this.closest('.form-group').style.zIndex = '10';
            });

            field.addEventListener('blur', function() {
                this.closest('.form-group').style.transform = 'translateX(0) scale(1)';
                this.closest('.form-group').style.zIndex = '1';
            });

            // Add validation feedback
            field.addEventListener('change', function() {
                if (this.value && this.checkValidity()) {
                    this.style.borderColor = '#48bb78';
                    this.style.boxShadow = '0 0 0 3px rgba(72, 187, 120, 0.1)';
                } else if (this.value) {
                    this.style.borderColor = '#e53e3e';
                    this.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
                }
            });
        });

        // Medical parallax effect
        document.addEventListener('mousemove', function(e) {
            const elements = document.querySelectorAll('.medical-element');
            const x = (e.clientX / window.innerWidth) * 100;
            const y = (e.clientY / window.innerHeight) * 100;

            elements.forEach((element, index) => {
                const speed = (index + 1) * 0.6;
                const rotationSpeed = (index + 1) * 0.3;
                element.style.transform = `translate(${x * speed * 0.02}px, ${y * speed * 0.02}px) rotate(${x * rotationSpeed}deg)`;
            });
        });

        // Add typing feedback
        document.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('input', function() {
                this.style.transform = 'scale(1.01)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Date picker enhancement
        document.getElementById('actual_date')?.addEventListener('change', function() {
            if (this.value) {
                const selectedDate = new Date(this.value);
                const today = new Date();
                
                if (selectedDate > today) {
                    this.style.borderColor = '#feca57';
                    this.style.boxShadow = '0 0 0 3px rgba(254, 202, 87, 0.1)';
                } else {
                    this.style.borderColor = '#48bb78';
                    this.style.boxShadow = '0 0 0 3px rgba(72, 187, 120, 0.1)';
                }
            }
        });

        // Success message auto-hide (if needed in future)
        const successMsg = document.querySelector('.success-message');
        if (successMsg) {
            setTimeout(() => {
                successMsg.style.animation = 'successPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) reverse forwards';
            }, 5000);
        }
    </script>
</body>
</html>