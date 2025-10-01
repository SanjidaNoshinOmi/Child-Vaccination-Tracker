<?php
session_start();
if(!isset($_SESSION['UserId']) || $_SESSION['Role'] != 'admin'){
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
	<link rel="stylesheet" href="css/admin.css">  <!-- external CSS file -->
    <title>Admin Dashboard - Child Vaccination Tracker</title>
</head>
<body>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <div class="admin-badge">
        Admin Panel
    </div>

    <header>
        <nav>
            <div class="nav_logo">
                <h1><a href="admin_dashboard.php">Child Vaccination Tracker</a></h1>
            </div>
            <ul class="nav_link">
                <li><a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=child_vaccination_tracker" target="_blank">Database</a></li>
                <li><a href="insert_vaccine.php">Add Vaccines</a></li>
                <li><a href="insert_center.php">Add Centers</a></li>
                <li><a href="show_centers.php">View Centers</a></li>
                <li><a href="show_vaccines.php">View Vaccines</a></li>
                <li><a href="logout.php" onclick="return confirm('Log out of your account?');">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="dashboard">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['Name']); ?>!</h1>
            <p>Complete administrative control over the Child Vaccination Tracking System. Manage vaccines, centers, and monitor all vaccination activities.</p>
            
            <div class="quick-actions">
                <a href="insert_vaccine.php" class="action-card">
                    <span class="icon">💉</span>
                    <h3>Insert Vaccines</h3>
                    <p>Add new vaccines to the system and update vaccination schedules</p>
                </a>
                
                <a href="insert_center.php" class="action-card">
                    <span class="icon">🏥</span>
                    <h3>Insert Centers</h3>
                    <p>Register new vaccination centers and update facility information</p>
                </a>
                
                <a href="show_centers.php" class="action-card">
                    <span class="icon">📍</span>
                    <h3>View Centers</h3>
                    <p>Browse all registered vaccination centers and their details</p>
                </a>
                
                <a href="show_vaccines.php" class="action-card">
                    <span class="icon">📋</span>
                    <h3>View Vaccines</h3>
                    <p>Monitor all available vaccines and their specifications</p>
                </a>
                
                <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=child_vaccination_tracker" target="_blank" class="action-card">
                    <span class="icon">🗄️</span>
                    <h3>Database</h3>
                    <p>Direct access to phpMyAdmin for advanced database management</p>
                </a>
            </div>
        </section>
    </main>
</body>
</html>
