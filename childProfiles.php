<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    header("Location: index.php");
    exit();
}

$userid = $_SESSION['UserId'];
$result = null;
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = "%" . strtolower(trim($_GET['search'])) . "%";
    $sql = "SELECT * FROM child_profiles WHERE UserId = ? AND LOWER(Name) LIKE ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "is", $userid, $search_query);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM child_profiles WHERE UserId = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "i", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

function calculateAgeDetailed($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $diff = $today->diff($birthDate);

    $years = $diff->y;
    $months = $diff->m;
    $days = $diff->d;
    $weeks = intdiv($days, 7);

    $parts = [];
    if ($years > 0) $parts[] = $years . " year" . ($years > 1 ? "s" : "");
    if ($months > 0) $parts[] = $months . " month" . ($months > 1 ? "s" : "");
    if ($weeks > 0) $parts[] = $weeks . " week" . ($weeks > 1 ? "s" : "");

    if (empty($parts)) {
        $parts[] = $days . " day" . ($days > 1 ? "s" : "");
    }

    return implode(", ", $parts);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Child Profiles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/show_childProfiles.css">
</head>
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
                <li><a href="register_ChildProfiles.php">📋 Register Child</a></li>
                <li><a href="childProfiles.php">👶 Child Profiles</a></li>
                <li><a href="payment.php">💳 Payment</a></li>
                <li><a href="show_vaccines.php">💉 Vaccines</a></li>
                <li><a href="show_centers.php">🏥 Centers</a></li>
                <li><a href="childVaccination.php">💊 Child Vaccination</a></li>
                <li><a href="logout.php" onclick="return confirm('Log out of your account?');"> 🚪Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 style="text-align: center; font-family: 'Fredoka', cursive; font-size: 2.5rem; color: var(--secondary-color);">👶 My Child Profiles</h1>
        
        <?php if (mysqli_num_rows($result) > 0) { ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Guardian</th>
                            <th>Health Card</th>
                            <th>Vaccination</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ChildId']); ?></td>
                            <td><?php echo htmlspecialchars($row['Name']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateOfBirth']); ?></td>
                            <td><?php echo calculateAgeDetailed($row['DateOfBirth']); ?></td>
                            <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['GuardianName']); ?></td>
                            <td>
                                <?php
                                $check_sql = "SELECT HealthCardId FROM healthcard WHERE ChildId = ?";
                                $check_stmt = mysqli_prepare($conn, $check_sql);
                                mysqli_stmt_bind_param($check_stmt, "i", $row['ChildId']);
                                mysqli_stmt_execute($check_stmt);
                                $check_result = mysqli_stmt_get_result($check_stmt);

                                if (mysqli_num_rows($check_result) > 0) {
                                    echo '<a href="healthcard.php?childId=' . htmlspecialchars($row['ChildId']) . '" class="btn-link">View Card</a>';
                                } else {
                                    echo '<a href="add_healthcard.php?childId=' . htmlspecialchars($row['ChildId']) . '" class="btn-link">Add Card</a>';
                                }
                                mysqli_stmt_close($check_stmt);
                                ?>
                            </td>
                            <td>
                                <a href="show_childVaccination.php?childId=<?php echo htmlspecialchars($row['ChildId']); ?>" class="btn-link">View Status</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <h2>No Profiles Found 🤷‍♀️</h2>
                <p>It seems no profiles match your search or you haven't registered any children yet. 
                   <a href="register_ChildProfiles.php" class="btn-link">Register a child here</a>.
                </p>
            </div>
        <?php } ?>
    </div>
</body>
</html>