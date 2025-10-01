<?php
session_start();
include 'DBconnect.php';

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap"
      rel="stylesheet"
    />
	<link rel="stylesheet" href="css/general.css">  <!-- external CSS file -->
    <title>General Dashboard - Child Vaccination Tracker</title>
<body>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <div class="user-badge">
        General User
    </div>

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

    <main>
        <section class="dashboard">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['Name']); ?>!</h1>
            <p>Your comprehensive dashboard for managing child vaccination records, scheduling appointments, and tracking immunization progress.</p>
            
            <div class="quick-actions">
                <a href="register_ChildProfiles.php" class="action-card">
                    <span class="icon">👶</span>
                    <h3>Register Child</h3>
                    <p>Add a new child profile and begin tracking their vaccination journey</p>
                </a>
                
                <a href="childProfiles.php" class="action-card">
                    <span class="icon">👨‍👩‍👧‍👦</span>
                    <h3>Child Profiles</h3>
                    <p>View and manage all registered children and their detailed information</p>
                </a>
                
                <a href="childVaccination.php" class="action-card">
                    <span class="icon">💉</span>
                    <h3>Vaccinations</h3>
                    <p>Schedule appointments and track vaccination status for all children</p>
                </a>
                
                <a href="show_vaccines.php" class="action-card">
                    <span class="icon">📋</span>
                    <h3>Available Vaccines</h3>
                    <p>Browse all available vaccines and their recommended schedules</p>
                </a>
                
                <a href="show_centers.php" class="action-card">
                    <span class="icon">🏥</span>
                    <h3>Centers</h3>
                    <p>Find nearby vaccination centers and their contact information</p>
                </a>
                
                <a href="payment.php" class="action-card">
                    <span class="icon">💳</span>
                    <h3>Payment</h3>
                    <p>Manage payments for vaccination services and view transaction history</p>
                </a>
            </div>
        </section>
    </main>
</body>
</html>