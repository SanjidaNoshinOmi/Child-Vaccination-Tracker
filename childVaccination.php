<?php
session_start();
require_once('DBconnect.php');

// Ensure only general users can access
if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    die("Access denied. Please log in as a general user.");
}

$message = "";
$childId = 0; // Initialize $childId

// Handle POST request from the first form submission (selecting child)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['childId'])) {
    $childId = intval($_POST['childId']);
}

// Handle POST from the second form submission (saving vaccines)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_vaccines'])) {
    $childId = intval($_POST['childId']);

    foreach ($_POST['vaccine_data'] as $vaccineId => $data) {
        $centerId = isset($data['center_id']) ? intval($data['center_id']) : NULL;
        $doseNumber = isset($data['dose_number']) ? intval($data['dose_number']) : NULL;

        // Validate Actual Date
        $actualDateInput = isset($data['actual_date']) ? trim($data['actual_date']) : '';
        $actualDate = NULL;
        if (!empty($actualDateInput)) {
            $d = DateTime::createFromFormat('Y-m-d', $actualDateInput);
            if ($d && $d->format('Y-m-d') === $actualDateInput) {
                $actualDate = $actualDateInput; // Valid date
            }
        }

        // Status logic
        $status = isset($data['completed']) ? "Completed" : (empty($actualDate) ? "Missed" : "Completed");

        $scheduledDate = NULL; // Optional, you can set default scheduled date if needed

        // Check if record exists
        $checkSql = "SELECT RecordId FROM childvaccination WHERE ChildId = ? AND VaccineId = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "ii", $childId, $vaccineId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        mysqli_stmt_close($checkStmt);

        if (mysqli_num_rows($checkResult) == 0) {
            // Insert new record
            $sql = "INSERT INTO childvaccination (ChildId, VaccineId, CenterId, Dose_Number, Status, ScheduledDate, ActualDate)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiiisss", $childId, $vaccineId, $centerId, $doseNumber, $status, $scheduledDate, $actualDate);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            // Update existing record
            $updateSql = "UPDATE childvaccination 
                          SET CenterId = ?, Dose_Number = ?, Status = ?, ScheduledDate = ?, ActualDate = ?
                          WHERE ChildId = ? AND VaccineId = ?";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "iissiii", $centerId, $doseNumber, $status, $scheduledDate, $actualDate, $childId, $vaccineId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        }
    }

    $message = "<p style='color:green;'>✅ Vaccination records updated successfully.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Vaccination Record - Medical Excellence</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Fredoka:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/childVaccination.css">

</head>


<body>
 <div class="medical-bg">
        <div class="floating-medical medical-1">💉</div>
        <div class="floating-medical medical-2">🏥</div>
        <div class="floating-medical medical-3">👶</div>
        <div class="floating-medical medical-4">💊</div>
        <div class="floating-medical medical-5">🩺</div>
        <div class="floating-medical medical-6">📋</div>
    </div>
    <div class="particles">
        <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 1s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 3s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 2.5s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 4.5s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 1.5s;"></div>
    </div>

    <div class="main-container">
        <div class="page-header">
            <h1>🩺 Record Child Vaccination</h1>
            <p class="subtitle">Comprehensive Child Immunization Management</p>
        </div>

        <?php if ($message) { ?>
            <div class="success-message">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <div class="child-selection">
            <div class="selection-header">
                <div class="selection-icon">👶</div>
                <h2>Select Your Child</h2>
            </div>
    
	

    <form method="post">
        <label for="childId">Select Child:</label>
        <select name="childId" required onchange="this.form.submit()">
            <option value="">-- Select --</option>
            <?php
            $userId = $_SESSION['UserId'];
            $res = mysqli_query($conn, "SELECT ChildId, Name FROM child_profiles WHERE UserId=$userId");
            while ($row = mysqli_fetch_assoc($res)) {
                $selected = (isset($_POST['childId']) && $_POST['childId'] == $row['ChildId']) ? "selected" : "";
                echo "<option value='{$row['ChildId']}' $selected>".htmlspecialchars($row['Name'])."</option>";
            }
            ?>
        </select><br><br>
    </form>
    
	
	  <?php
        if ($childId > 0) {
            $resAge = mysqli_query($conn, "SELECT TIMESTAMPDIFF(MONTH, DateOfBirth, CURDATE()) AS AgeMonths FROM child_profiles WHERE ChildId=$childId");
            $rowAge = mysqli_fetch_assoc($resAge);
            $childAgeMonths = $rowAge['AgeMonths'];
        ?>

        <div class="vaccination-schedule">
            <div class="schedule-header">
                <h3>💉 Vaccination Schedule</h3>
                <p class="age-display">Child's Current Age: <strong><?php echo $childAgeMonths; ?> months</strong></p>
            </div>
            <form method='post' id="vaccineForm">
                <input type='hidden' name='childId' value='<?php echo $childId; ?>'>
                <input type='hidden' name='save_vaccines' value='1'>
                <div class="vaccines-grid">
                    <?php
                    $res = mysqli_query($conn, "SELECT VaccineId, Name, Dose_Number, Recommended_Age FROM vaccine");
                    while ($row = mysqli_fetch_assoc($res)) {
                        $vid = $row['VaccineId'];
                        $ageText = strtolower($row['Recommended_Age']);
                        $vaccineAgeMonths = 0;
                        
                        if (strpos($ageText, "week") !== false) {
                            $num = intval($ageText);
                            $vaccineAgeMonths = ceil($num / 4);
                        } elseif (strpos($ageText, "year") !== false) {
                            $num = intval($ageText);
                            $vaccineAgeMonths = $num * 12;
                        } elseif (strpos($ageText, "month") !== false) {
                            $num = intval($ageText);
                            $vaccineAgeMonths = $num;
                        } elseif ($ageText == "birth") {
                            $vaccineAgeMonths = 0;
                        }

                        if ($vaccineAgeMonths <= $childAgeMonths) {
                    ?>
                        <div class="vaccine-card">
                            <div class="vaccine-header">
                                <div class="vaccine-icon">💉</div>
                                <div class="vaccine-info">
                                    <h4><?php echo htmlspecialchars($row['Name']); ?></h4>
                                    <p class="recommended-age">Dose <?php echo $row['Dose_Number']; ?> • Recommended: <?php echo htmlspecialchars($row['Recommended_Age']); ?></p>
                                </div>
                            </div>
                            <div class="vaccine-form-grid">
                                <div class="form-field">
                                    <label>📅 Actual Date</label>
                                    <input type='date' name='vaccine_data[<?php echo $vid; ?>][actual_date]' />
                                </div>
                                <div class="form-field">
                                    <label>💊 Dose Number</label>
                                    <input type='number' name='vaccine_data[<?php echo $vid; ?>][dose_number]' value='<?php echo $row['Dose_Number']; ?>' min='0' />
                                </div>
                                <div class="form-field">
                                    <label>🏥 Vaccination Center</label>
                                    <select name='vaccine_data[<?php echo $vid; ?>][center_id]'>
                                        <option value="">Select Center</option>
                                        <?php
                                        $cRes = mysqli_query($conn, "SELECT CenterId, Name FROM centers");
                                        while ($cRow = mysqli_fetch_assoc($cRes)) {
                                            echo "<option value='{$cRow['CenterId']}'>".htmlspecialchars($cRow['Name'])."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="checkbox-field">
                                    <label class="custom-checkbox">
                                        <input type='checkbox' name='vaccine_data[<?php echo $vid; ?>][completed]'>
                                        <span>✅ Mark as Completed</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php
                        }
                    }
                    ?>
                </div>
                <div class="action-buttons">
                    <button type='submit' class="btn" id="saveBtn">
                        💾 Save Vaccination Records
                    </button>
                    <a href='show_childVaccination.php?childId=<?php echo $childId; ?>' class="btn btn-secondary">
                        📊 View Vaccination Status
                    </a>
                </div>
            </form>
        </div>
        <?php } ?>

        <div class="nav-links">
            <a href="general_dashboard.php" class="btn btn-link">
                ⬅️ Back to Dashboard
            </a>
        </div>
    </div>
    
    <script>
        // Show loading animation when child is selected
        function showLoading() {
            const loadingDiv = document.getElementById('loadingDiv');
            loadingDiv.classList.add('active');
        }

        // Add interactive effects to vaccine cards
        document.querySelectorAll('.vaccine-card').forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(-5px) scale(1)';
            });
        });

        // Enhanced checkbox interactions
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.vaccine-card');
                if (this.checked) {
                    card.style.background = 'linear-gradient(135deg, rgba(17, 153, 142, 0.2), rgba(56, 239, 125, 0.2))';
                    card.style.borderColor = 'rgba(17, 153, 142, 0.5)';
                } else {
                    card.style.background = 'rgba(255, 255, 255, 0.1)';
                    card.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                }
            });
        });

        // Form submission with loading effect
        document.getElementById('vaccineForm')?.addEventListener('submit', function() {
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.innerHTML = '⏳ Saving Records...';
            saveBtn.style.pointerEvents = 'none';
            saveBtn.style.opacity = '0.7';
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Enhanced form field interactions
        document.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('focus', function() {
                this.closest('.form-field')?.style.transform = 'translateY(-2px)';
                this.closest('.vaccine-card')?.style.boxShadow = '0 30px 80px rgba(0, 0, 0, 0.5)';
            });
            field.addEventListener('blur', function() {
                this.closest('.form-field')?.style.transform = 'translateY(0)';
                this.closest('.vaccine-card')?.style.boxShadow = '0 20px 60px rgba(0, 0, 0, 0.3)';
            });
        });

        // Add CSS for ripple effect dynamically
        const style = document.createElement('style');
        style.textContent = `
            .btn {
                position: relative;
                overflow: hidden;
            }
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            .vaccine-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .form-field {
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>