<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId'])) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM vaccine";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching vaccines: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/vaccine.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Vaccine Details - Child Vaccination Tracker</title>
	<link rel="stylesheet" href="css/vaccine.css"> 
</head>

<body>
    <div class="medical-icons">
      <div class="medical-icon">💉</div>
      <div class="medical-icon">🏥</div>
      <div class="medical-icon">👶</div>
      <div class="medical-icon">🩺</div>
    </div>
    <div class="floating-elements">
      <div class="floating-element">💉</div>
      <div class="floating-element">💊</div>
      <div class="floating-element">🧬</div>
      <div class="floating-element">🏥</div>
	  <div class="floating-element">🩺</div>
	  <div class="floating-element">👶</div>
	  <div class="floating-element">🧪</div>
    </div>

    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?php echo ($_SESSION['Role'] === 'admin') ? 'admin_dashboard.php' : 'general_dashboard.php'; ?>" class="nav-logo">
                Child Vaccination Tracker
            </a>
            <ul class="nav-links">
                <?php if ($_SESSION['Role'] === 'admin') { ?>
                    <li><a href="admin_dashboard.php">🏠 Dashboard</a></li>
                    <li><a href="insert_vaccine.php">➕ Add Vaccine</a></li>
                    <li><a href="show_centers.php">🏥 Centers</a></li>
                <?php } else { ?>
                    <li><a href="general_dashboard.php">🏠 Dashboard</a></li>
                    <li><a href="childProfiles.php">👶 Profiles</a></li>
                    <li><a href="show_centers.php">🏥 Centers</a></li>
                <?php } ?>
                <li><a href="logout.php" onclick="return confirm('Log out of your account?');">🚪 Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Available Vaccines</h1>
            <p class="header-subtitle">Comprehensive vaccine information and management</p>
        </div>

        <div class="search-container">
            <input type="text" class="search-input" id="vaccineSearch" placeholder="🔍 Search vaccines by name, type, or age..." onkeyup="searchVaccines()">
        </div>

        <div class="action-bar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: rgba(255,255,255,0.9); font-weight: 500;">Vaccine Management</span>
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <?php if ($_SESSION['Role'] === 'admin') { ?>
                    <a href="insert_vaccine.php" class="btn btn-primary">
                        ➕ Add New Vaccine
                    </a>
                <?php } ?>
                <a href="<?php echo ($_SESSION['Role'] === 'admin') ? 'admin_dashboard.php' : 'general_dashboard.php'; ?>" class="btn btn-secondary">
                    🏠 Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <div class="table-container">
                <table id="vaccineTable">
                    <thead>
                        <tr>
                            <th class="tooltip">
                                Vaccine ID
                                <span class="tooltip-text">Unique identifier for each vaccine</span>
                            </th>
                            <th>Name</th>
                            <th class="tooltip">
                                Dose Number
                                <span class="tooltip-text">Which dose in the vaccine series</span>
                            </th>
                            <th>Type</th>
                            <th class="tooltip">
                                Recommended Age
                                <span class="tooltip-text">Age when vaccine should be administered</span>
                            </th>
                            <th>Description</th>
                            <th class="tooltip">
                                Side Effects
                                <span class="tooltip-text">Common side effects after vaccination</span>
                            </th>
                            <?php if ($_SESSION['Role'] === 'admin') { ?>
                                <th>Actions</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $delay = 0.1;
                        while ($row = mysqli_fetch_assoc($result)) { 
                        ?>
                            <tr class="table-row-animation" style="animation-delay: <?php echo $delay; ?>s">
                                <td><strong><?php echo htmlspecialchars($row["VaccineId"]); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($row["Name"]); ?></strong></td>
                                <td><?php echo htmlspecialchars($row["Dose_Number"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Type"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Recommended_Age"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Description"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Aftereffects"]); ?></td>
                                <?php if ($_SESSION['Role'] === 'admin') { ?>
                                    <td>
                                        <div class="enhanced-actions">
                                            <a href="edit_vaccine.php?id=<?php echo $row["VaccineId"]; ?>" class="action-btn edit-btn">
                                                ✏️ Edit
                                            </a>
                                            <a href="delete_vaccine.php?id=<?php echo $row["VaccineId"]; ?>" 
                                               class="action-btn delete-btn"
                                               onclick="return confirm('⚠️ Are you sure you want to delete this vaccine? This action cannot be undone.');">
                                                🗑️ Delete
                                            </a>
                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php 
                            $delay += 0.1;
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="enhanced-empty-state">
                <h2>No Vaccines Found</h2>
                <p>There are currently no vaccines in the database. <?php if ($_SESSION['Role'] === 'admin') { ?>Add some vaccines to get started!<?php } ?></p>
                <?php if ($_SESSION['Role'] === 'admin') { ?>
                    <a href="insert_vaccine.php" class="btn btn-primary">
                        ➕ Add First Vaccine
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <script>
        function searchVaccines() {
            const input = document.getElementById('vaccineSearch');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('vaccineTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                if (found) {
                    row.style.display = '';
                    row.style.animation = 'fadeInRow 0.5s ease-out';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.table-row-animation');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 100);
            });
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            const navLinks = document.querySelectorAll('.nav-links a');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href').includes('#')) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            });
        });
        function confirmDelete(vaccineName) {
            return confirm(`⚠️ Delete Vaccine: ${vaccineName}\n\nThis action cannot be undone. Are you sure you want to proceed?`);
        }
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                document.getElementById('vaccineSearch').focus();
            }
        });
        window.addEventListener('beforeunload', function() {
            document.body.style.opacity = '0.7';
            document.body.style.transition = 'opacity 0.3s ease';
        });
    </script>
</body>
</html>