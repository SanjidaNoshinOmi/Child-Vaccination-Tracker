<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    header("Location: index.php");
    exit();
}

$childId = isset($_GET['childId']) ? intval($_GET['childId']) : 0;
if ($childId === 0) die("Invalid request.");

// Get child + healthcard info
$sql = "SELECT cp.*, hc.* FROM child_profiles cp
        INNER JOIN healthcard hc ON cp.ChildId = hc.ChildId
        WHERE cp.ChildId = ? AND cp.UserId = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $childId, $_SESSION['UserId']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) die("Healthcard not found.");
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$allergyStatus = !empty($data['Allergies']) ? "Yes" : "No";
$downloadDate = date('Y-m-d', strtotime($data['DownloadDate']));
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcard - <?= htmlspecialchars($data['Name']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
     <link rel="stylesheet" href="css/healthcard.css">
</head>
<body>
    <div class="loading">
        <div class="spinner"></div>
    </div>
    <div class="bg-animation" id="bgAnimation"></div>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heartbeat"></i> Digital Healthcard</h1>
            <p class="subtitle">Comprehensive Health Information System</p>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="profile-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2><?= htmlspecialchars($data['Name']) ?></h2>
            </div>

            <div class="info-grid">
                <div class="info-section">
                    <div class="section-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-card"></i> Child ID</span>
                        <span class="info-value"><?= htmlspecialchars($data['ChildId']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar"></i> Date of Birth</span>
                        <span class="info-value"><?= htmlspecialchars($data['DateOfBirth']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-venus-mars"></i> Gender</span>
                        <span class="info-value"><?= htmlspecialchars($data['Gender']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-tint"></i> Blood Group</span>
                        <span class="info-value status-indicator status-red"><?= htmlspecialchars($data['BloodGroup']) ?></span>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title">
                        <i class="fas fa-home"></i>
                        Family Information
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-male"></i> Father Name</span>
                        <span class="info-value"><?= htmlspecialchars($data['FatherName']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-female"></i> Mother Name</span>
                        <span class="info-value"><?= htmlspecialchars($data['MotherName']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-phone"></i> Guardian Contact</span>
                        <span class="info-value"><?= htmlspecialchars($data['GuardianContact']) ?></span>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title">
                        <i class="fas fa-weight"></i>
                        Physical Information
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-weight-hanging"></i> Weight</span>
                        <span class="info-value"><?= htmlspecialchars($data['Weight']) ?> kg</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-ruler-vertical"></i> Height</span>
                        <span class="info-value"><?= htmlspecialchars($data['Height']) ?> cm</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-check"></i> Download Date</span>
                        <span class="info-value"><?= htmlspecialchars($downloadDate) ?></span>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title">
                        <i class="fas fa-stethoscope"></i>
                        Medical Information
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-syringe"></i> Vaccination Status</span>
                        <span class="info-value status-indicator status-green"><?= htmlspecialchars($data['VaccinatedStatus']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-exclamation-triangle"></i> Allergies</span>
                        <span class="info-value status-indicator <?= $allergyStatus === 'Yes' ? 'status-yellow' : 'status-green' ?>"><?= htmlspecialchars($allergyStatus) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-heartbeat"></i> Chronic Illness</span>
                        <span class="info-value"><?= htmlspecialchars($data['ChronicIllness']) ?: 'None' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-pills"></i> Medications</span>
                        <span class="info-value"><?= htmlspecialchars($data['Medications']) ?: 'None' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-notes-medical"></i> Doctor Notes</span>
                        <span class="info-value"><?= htmlspecialchars($data['Notes']) ?: 'None' ?></span>
                    </div>
                </div>
            </div>

            <div class="download-section">
                <button class="download-btn" onclick="window.print()">
                    <i class="fas fa-download"></i>
                    Download / Print Healthcard
                </button>
            </div>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const bgAnimation = document.getElementById('bgAnimation');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
                bgAnimation.appendChild(particle);
            }
        }

        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Add stagger animation to info items
            const infoItems = document.querySelectorAll('.info-item');
            infoItems.forEach((item, index) => {
                item.style.animationDelay = (index * 0.1) + 's';
                item.style.animation = 'slideInLeft 0.6s ease-out forwards';
            });
        });

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>