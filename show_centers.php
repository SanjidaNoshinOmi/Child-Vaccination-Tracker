<?php
session_start();
require_once('DBconnect.php');
if (!isset($_SESSION['UserId'])) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM centers";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching centers: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/center.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Vaccination Centers - Child Vaccination Tracker</title>
</head>
<body>
	<div class="floating-elements">
      <div class="floating-element">🏥</div>
      <div class="floating-element">📍</div>
      <div class="floating-element">📞</div>
      <div class="floating-element">🗺️</div>
	  <div class="floating-element">🩺</div>
	  <div class="floating-element">🏢</div>
	  <div class="floating-element">🏪</div>
	  <div class="floating-element">🚑</div>
	  <div class="floating-element">🏫</div>
    </div>

    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?php echo ($_SESSION['Role'] === 'admin') ? 'admin_dashboard.php' : 'general_dashboard.php'; ?>" class="nav-logo">
                Child Vaccination Tracker
            </a>
            <ul class="nav-links">
                <?php if ($_SESSION['Role'] === 'admin') { ?>
                    <li><a href="admin_dashboard.php">🏠 Dashboard</a></li>
                    <li><a href="insert_center.php">➕ Add Center</a></li>
                    <li><a href="show_vaccines.php">💉 Vaccines</a></li>
                <?php } else { ?>
                    <li><a href="general_dashboard.php">🏠 Dashboard</a></li>
                    <li><a href="childProfiles.php">👶 Profiles</a></li>
                    <li><a href="show_vaccines.php">💉 Vaccines</a></li>
                <?php } ?>
                <li><a href="logout.php" onclick="return confirm('Log out of your account?');">🚪 Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <div class="header">
            <h1>Vaccination Centers</h1>
            <div class="vaccine-icons">
                <span class="vaccine-icon">🏥</span>
                <span class="vaccine-icon">🩺</span>
                <span class="vaccine-icon">👶</span>
                <span class="vaccine-icon">💉</span>
            </div>
        </div>

        <div class="search-container">
            <input type="text" class="search-input" id="centerSearch" placeholder="🔍 Search centers by name, address, or contact..." onkeyup="searchCenters()">
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: rgba(255,255,255,0.9); font-weight: 500;">Center Management</span>
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <?php if ($_SESSION['Role'] === 'admin') { ?>
                    <a href="insert_center.php" class="btn btn-center-primary">
                        ➕ Add New Center
                    </a>
                <?php } ?>
                <a href="<?php echo ($_SESSION['Role'] === 'admin') ? 'admin_dashboard.php' : 'general_dashboard.php'; ?>" class="btn btn-secondary">
                    🏠 Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <div class="table-container">
                <table id="centerTable">
                    <thead>
                        <tr>
                            <th class="tooltip">
                                Center ID
                                <span class="tooltip-text">Unique identifier for each center</span>
                            </th>
                            <th>Center Name</th>
                            <th class="tooltip">
                                Address
                                <span class="tooltip-text">Physical location of the center</span>
                            </th>
                            <th class="tooltip">
                                Contact
                                <span class="tooltip-text">Phone number or contact information</span>
                            </th>
                            <th class="tooltip">
                                Available Vaccines
                                <span class="tooltip-text">Types of vaccines available at this center</span>
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
                                <td class="center-id"><?php echo htmlspecialchars($row["CenterId"]); ?></td>
                                <td class="center-name"><?php echo htmlspecialchars($row["Name"]); ?></td>
                                <td class="address-cell"><?php echo htmlspecialchars($row["Address"]); ?></td>
                                <td class="contact-cell"><?php echo htmlspecialchars($row["Contact"]); ?></td>
                                <td class="vaccines-cell"><?php echo htmlspecialchars($row["AvailableVaccines"]); ?></td>
                                <?php if ($_SESSION['Role'] === 'admin') { ?>
                                    <td>
                                        <div class="enhanced-actions">
                                            <a href="edit_center.php?id=<?php echo $row["CenterId"]; ?>" class="action-btn edit-btn">
                                                ✏️ Edit
                                            </a>
                                            <a href="delete_center.php?id=<?php echo $row["CenterId"]; ?>"
                                               class="action-btn delete-btn"
                                               onclick="return confirm('⚠️ Are you sure you want to delete this center?\n\nCenter: <?php echo htmlspecialchars($row["Name"]); ?>\n\nThis action cannot be undone.');">
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
            <!-- Enhanced Empty State -->
            <div class="enhanced-empty-state">
                <h2>No Centers Found</h2>
                <p>There are currently no vaccination centers in the database. <?php if ($_SESSION['Role'] === 'admin') { ?>Add some centers to get started!<?php } ?></p>
                <?php if ($_SESSION['Role'] === 'admin') { ?>
                    <a href="insert_center.php" class="btn btn-center-primary">
                        ➕ Add First Center
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <script>
        // Enhanced search functionality
        function searchCenters() {
            const input = document.getElementById('centerSearch');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('centerTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                // Search through all cells except the last one (actions)
                const searchCells = cells.length - (<?php echo ($_SESSION['Role'] === 'admin') ? '1' : '0'; ?>);
                for (let j = 0; j < searchCells; j++) {
                    if (cells[j] && cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
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

        // Enhanced table row animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.table-row-animation');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 100);
            });

            // Enhanced hover effects for action buttons
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Smooth scroll for navigation
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

            // Add click-to-call functionality for phone numbers
            const contactCells = document.querySelectorAll('.contact-cell');
            contactCells.forEach(cell => {
                const text = cell.textContent.trim();
                // Simple phone number detection
                if (/[\d\s\-\+\(\)]+/.test(text) && text.length >= 7) {
                    cell.style.cursor = 'pointer';
                    cell.title = 'Click to call';
                    cell.addEventListener('click', function() {
                        const phoneNumber = text.replace(/\s/g, '');
                        window.location.href = `tel:${phoneNumber}`;
                    });
                }
            });

            // Add click functionality for addresses (open in maps)
            const addressCells = document.querySelectorAll('.address-cell');
            addressCells.forEach(cell => {
                cell.style.cursor = 'pointer';
                cell.title = 'Click to view on map';
                cell.addEventListener('click', function() {
                    const address = encodeURIComponent(this.textContent.trim());
                    window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank');
                });
            });
        });

        // Enhanced delete confirmation
        function confirmDeleteCenter(centerName, centerAddress) {
            return confirm(`⚠️ Delete Center: ${centerName}\nAddress: ${centerAddress}\n\nThis action cannot be undone. Are you sure you want to proceed?`);
        }

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                document.getElementById('centerSearch').focus();
            }
        });

        // Loading states for better UX
        window.addEventListener('beforeunload', function() {
            document.body.style.opacity = '0.7';
            document.body.style.transition = 'opacity 0.3s ease';
        });

        // Add fade-in animation keyframe for search results
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInRow {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>