<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    die("Access denied. Please log in as a general user.");
}

$userId = $_SESSION['UserId'];

if (!isset($_GET['childId'])) {
    die("No child selected.");
}
$childId = intval($_GET['childId']);

$childRes = mysqli_query($conn, "SELECT Name, DateOfBirth FROM child_profiles WHERE ChildId=$childId AND UserId=$userId");
$child = mysqli_fetch_assoc($childRes);
if (!$child) {
    die("Invalid child.");
}

$dob = $child['DateOfBirth'];
$ageRes = mysqli_query($conn, "SELECT TIMESTAMPDIFF(MONTH, '$dob', CURDATE()) AS AgeMonths");
$ageRow = mysqli_fetch_assoc($ageRes);
$childAgeMonths = $ageRow['AgeMonths'];

$checkRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM childvaccination WHERE ChildId=$childId");
$checkRow = mysqli_fetch_assoc($checkRes);
if ($checkRow['cnt'] == 0) {
    echo "<h1>Vaccination Status for " . htmlspecialchars($child['Name']) . "</h1>";
    echo "<p>No records yet ❌ Please <a href='childVaccination.php?childId=$childId'>fill up here</a>.</p>";
    exit();
}

// Convert Recommended_Age to months
function convertAgeToMonths($ageText) {
    $ageText = strtolower($ageText);
    if ($ageText == "birth") return 0;
    if (strpos($ageText, "week") !== false) return ceil(intval($ageText)/4);
    if (strpos($ageText, "month") !== false) return intval($ageText);
    if (strpos($ageText, "year") !== false) return intval($ageText) * 12;
    return 0;
}

// Calculate suggested schedule date
function getScheduledDate($dob, $ageText) {
    $months = convertAgeToMonths($ageText);
    $date = new DateTime($dob);
    $date->modify("+$months month");
    return $date->format('Y-m-d');
}

// Check overdue status
function overdueStatus($scheduledDate) {
    $today = new DateTime();
    $sched = new DateTime($scheduledDate);
    $diff = $today->diff($sched)->days;

    if ($today > $sched) {
        if ($diff <= 4) {
            $endDate = (clone $sched)->modify("+4 days")->format('Y-m-d');
            return ["grace", "Give between $scheduledDate and $endDate, otherwise consult doctor"];
        } else {
            return ["overdue", "⚠ Consult doctor for reschedule"];
        }
    }
    return ["ok", ""];
}

// Completed and missed vaccines
$recordsRes = mysqli_query($conn, "
    SELECT cv.RecordId, v.Name, cv.Dose_Number, cv.Status, cv.ScheduledDate, cv.ActualDate, c.Name AS CenterName
    FROM childvaccination cv
    JOIN vaccine v ON cv.VaccineId = v.VaccineId
    LEFT JOIN centers c ON cv.CenterId = c.CenterId
    WHERE cv.ChildId=$childId
    ORDER BY cv.ScheduledDate
");

$hasRecords = mysqli_num_rows($recordsRes) > 0;

// Track vaccine doses given/missed
$dosesGiven = []; // key: Vaccine Name + Dose
$suggestions = [];
$upcoming = [];

$vaccineRes = mysqli_query($conn, "SELECT VaccineId, Name, Dose_Number, Recommended_Age FROM vaccine ORDER BY Name, Dose_Number");
while ($v = mysqli_fetch_assoc($vaccineRes)) {
    $vid = $v['VaccineId'];
    $vAgeMonths = convertAgeToMonths($v['Recommended_Age']);
    $suggestedDate = getScheduledDate($dob, $v['Recommended_Age']);

    // Already given?
    $chkRes = mysqli_query($conn, "SELECT * FROM childvaccination WHERE ChildId=$childId AND VaccineId=$vid");
    $record = mysqli_fetch_assoc($chkRes);

    $doseKey = $v['Name'] . "_dose" . $v['Dose_Number'];

    if ($vAgeMonths <= $childAgeMonths) {
        // Due or missed
        if (!$record || $record['Status'] === 'Missed' || empty($record['ActualDate'])) {
            $v['SuggestedDate'] = $suggestedDate;
            $suggestions[] = $v;
        } else {
            $dosesGiven[$doseKey] = true;
        }
    } else {
        // Upcoming
        $prevDoseKey = $v['Name'] . "_dose" . ($v['Dose_Number'] - 1);
        $v['ScheduledDate'] = $suggestedDate;

        // If it's not the first dose, only require "consult doctor" if a previous dose was actually missed
        if ($v['Dose_Number'] > 1 && !isset($dosesGiven[$prevDoseKey])) {
            $chkPrev = mysqli_query($conn, "SELECT * FROM vaccine WHERE Name='{$v['Name']}' AND Dose_Number=" . ($v['Dose_Number'] - 1));
            $prevVac = mysqli_fetch_assoc($chkPrev);
            if ($prevVac) {
                $prevRec = mysqli_query($conn, "SELECT * FROM childvaccination WHERE ChildId=$childId AND VaccineId=" . $prevVac['VaccineId']);
                $prevRecRow = mysqli_fetch_assoc($prevRec);
                if ($prevRecRow && $prevRecRow['Status'] === 'Missed') {
                    $v['PrevGiven'] = false;
                } else {
                    $v['PrevGiven'] = true;
                }
            }
        } else {
            $v['PrevGiven'] = true;
        }
        $upcoming[] = $v;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vaccination Status</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/show_childVaccination.css">
</head>
<body>

    <div class="floating-elements">
        <span class="floating-element">💉</span>
        <span class="floating-element">🏥</span>
        <span class="floating-element">👶</span>
        <span class="floating-element">💊</span>
        <span class="floating-element">🩺</span>
        <span class="floating-element">✅</span>
        <span class="floating-element">📋</span>
        <span class="floating-element">🗓️</span>
    </div>

    <div class="container">
        <div class="header">
            <h1>Vaccination Status for <?php echo htmlspecialchars($child['Name']); ?></h1>
            <p class="header-subtitle">Age: <?php echo $childAgeMonths; ?> months</p>
        </div>

        <h2 class="section-title">✅ Completed/Missed Vaccines</h2>
        <?php if ($hasRecords) { ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Center</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($recordsRes)) {
                            $statusClass = ($r['Status'] === 'Completed') ? "completed-status" : "missed-status";
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['Name']); ?></td>
                            <td><?php echo $r['Dose_Number']; ?></td>
                            <td class="<?php echo $statusClass; ?>">
                                <?php
                                if ($r['Status'] === 'Completed') echo "✅ Completed";
                                else echo "❌ Missed";
                                ?>
                            </td>
                            <td><?php echo $r['ActualDate']; ?></td>
                            <td>
                                <?php if ($r['Status'] === 'Completed') echo htmlspecialchars($r['CenterName']); else echo "N/A"; ?>
                            </td>
                            <td>
                                <?php if ($r['Status'] !== 'Completed') { ?>
                                    <a href="edit_vaccineRecord.php?recordId=<?php echo $r['RecordId']; ?>" class="action-btn edit-btn">✏ Edit</a>
                                <?php } else { ?>
                                    <span class="completed-status">✅ Completed</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="enhanced-empty-state">
                <h2>No Records Found ❌</h2>
                <p>It seems there are no vaccination records for this child. Please <a href="childVaccination.php" class="btn btn-center-primary">add them here</a>.</p>
            </div>
        <?php } ?>

        <h2 class="section-title">💡 Due/Missed Vaccines</h2>
        <?php if (!empty($suggestions)) { ?>
            <form method="post" action="schedule_vaccine.php">
                <input type="hidden" name="childId" value="<?php echo $childId; ?>">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Vaccine</th>
                                <th>Dose</th>
                                <th>Recommended Age</th>
                                <th>Suggested Date</th>
                                <th>Action</th>
                                <th>Center</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suggestions as $s) {
                                [$statusType, $message] = overdueStatus($s['SuggestedDate']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['Name']); ?></td>
                                <td><?php echo $s['Dose_Number']; ?></td>
                                <td><?php echo $s['Recommended_Age']; ?></td>
                                <td class="<?php echo ($statusType === 'overdue' || $statusType === 'grace') ? 'overdue-status' : 'upcoming-status'; ?>">
                                    <?php echo $s['SuggestedDate']; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($statusType === "overdue" || $statusType === "grace") {
                                        echo "<span class='overdue-status'>$message</span>";
                                    } else { ?>
                                        <input type="date" name="scheduled_<?php echo $s['VaccineId']; ?>" value="<?php echo $s['SuggestedDate']; ?>" required>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($statusType === "overdue" || $statusType === "grace") { ?>
                                        N/A
                                    <?php } else { ?>
                                        <select name="center_<?php echo $s['VaccineId']; ?>">
                                            <?php
                                            $cRes = mysqli_query($conn, "SELECT CenterId, Name FROM centers");
                                            while ($cRow = mysqli_fetch_assoc($cRes)) {
                                                echo "<option value='{$cRow['CenterId']}'>".htmlspecialchars($cRow['Name'])."</option>";
                                            }
                                            ?>
                                        </select>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions" style="text-align: right; margin-top: 10px;">
                    <button type="submit" class="btn btn-center-primary">💾 Save Schedule</button>
                </div>
            </form>
        <?php } else { ?>
            <div class="enhanced-empty-state">
                <h2>No Due or Missed Vaccines ✅</h2>
                <p>All vaccinations due for this child's age have been recorded.</p>
            </div>
        <?php } ?>

        <h2 class="section-title">📅 Upcoming Vaccines</h2>
        <?php if (!empty($upcoming)) { ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Recommended Age</th>
                            <th>Scheduled Date</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $u) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['Name']); ?></td>
                            <td><?php echo $u['Dose_Number']; ?></td>
                            <td><?php echo $u['Recommended_Age']; ?></td>
                            <td class="upcoming-status">🕒 <?php echo $u['ScheduledDate']; ?></td>
                            <td>
                                <?php if (!$u['PrevGiven'] && $u['Dose_Number'] > 1) { ?>
                                    <span class="overdue-status">⚠ Consult doctor first (previous dose missed)</span>
                                <?php } else { ?>
                                    Scheduled normally
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="enhanced-empty-state">
                <h2>No Upcoming Vaccines Right Now ✅</h2>
                <p>All future vaccines are far off from the current age.</p>
            </div>
        <?php } ?>

        <div class="form-actions" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" class="btn btn-secondary">🖨️ Print / Save PDF</button>
            <a href="general_dashboard.php" class="btn btn-secondary">⬅ Return to Dashboard</a>
        </div>
    </div>
</body>
</html>