<?php
session_start();
require_once('DBconnect.php');
if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    die("Access denied. Please log in as a general user.");
}

$message = "";
$receipt = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $childId = intval($_POST['childId']);
    $userId = $_SESSION['UserId'];
    $centerId = intval($_POST['centerid']);
    $amount = $_POST['amount'];
    $method = $_POST['method'];

    $transactionId = "TXN" . time() . rand(100, 999);

    $sql = "INSERT INTO payment (PaymentDate, Amount, PaymentMethod, UserId, ChildId, CenterId) VALUES (NOW(), ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdii", $amount, $method, $userId, $childId, $centerId);

    if (mysqli_stmt_execute($stmt)) {
        $paymentId = mysqli_insert_id($conn);

        $child_query = mysqli_query($conn, "SELECT Name FROM child_profiles WHERE ChildId=$childId");
        $child = mysqli_fetch_assoc($child_query);

        $center_query = mysqli_query($conn, "SELECT Name, Contact, Address FROM centers WHERE CenterId=$centerId");
        $center = mysqli_fetch_assoc($center_query);

        $user_query = mysqli_query($conn, "SELECT Name, Phone FROM users WHERE UserId=$userId");
        $user = mysqli_fetch_assoc($user_query);

        $_SESSION['receipt'] = [
            "TransactionId" => $transactionId,
            "PaymentId" => $paymentId,
            "ChildName" => $child['Name'],
            "CenterName" => $center['Name'],
            "CenterContact" => $center['Contact'],
            "CenterAddress" => $center['Address'],
            "UserName" => $user['Name'],
            "UserPhone" => $user['Phone'],
            "Amount" => $amount,
            "Method" => $method,
            "Date" => date("Y-m-d H:i:s")
        ];

        header("Location: payment.php");
        exit();

    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

if (isset($_SESSION['receipt'])) {
    $receipt = $_SESSION['receipt'];
    // This will prevent the receipt from showing up again on a refresh
    unset($_SESSION['receipt']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment System - Child Vaccination Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <div class="payment-icons">
        <div class="payment-icon">💳</div>
        <div class="payment-icon">💰</div>
        <div class="payment-icon">🏦</div>
        <div class="payment-icon">📱</div>
        <div class="payment-icon">💸</div>
    </div>

    <nav class="navbar">
        <div class="navbar-content">
            <a href="general_dashboard.php" class="nav-logo">
                Child Vaccination Tracker
            </a>
            <ul class="nav-links">
                <li><a href="general_dashboard.php">🏠 Dashboard</a></li>
                <li><a href="childProfiles.php">👶 Profiles</a></li>
                <li><a href="show_vaccines.php">💉 Vaccines</a></li>
                <li><a href="show_centers.php">🏥 Centers</a></li>
                <li><a href="logout.php" onclick="return confirm('Log out of your account?');">🚪 Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-container">
        <div class="payment-wrapper">
            <div class="payment-form-section">
                <div class="form-header">
                    <h1>Secure Payment</h1>
                    <p>Complete your vaccination payment safely</p>
                </div>

                <?php if ($message) echo '<div class="error-message">' . $message . '</div>'; ?>

                <form method="post" action="payment.php" class="payment-form" id="paymentForm">
                    <div class="form-group">
                        <label class="form-label" for="childId">👶 Select Child</label>
                        <select name="childId" id="childId" required class="form-select">
                            <option value="">Choose your child...</option>
                            <?php
                            $userId = $_SESSION['UserId'];
                            $sql_children = "SELECT ChildId, Name FROM child_profiles WHERE UserId = ?";
                            $stmt_children = mysqli_prepare($conn, $sql_children);
                            mysqli_stmt_bind_param($stmt_children, "i", $userId);
                            mysqli_stmt_execute($stmt_children);
                            $children_result = mysqli_stmt_get_result($stmt_children);

                            if (mysqli_num_rows($children_result) > 0) {
                                while ($child_row = mysqli_fetch_assoc($children_result)) {
                                    echo "<option value='" . htmlspecialchars($child_row['ChildId']) . "'>" . htmlspecialchars($child_row['Name']) . " (ID: " . htmlspecialchars($child_row['ChildId']) . ")</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No children found</option>";
                            }
                            mysqli_stmt_close($stmt_children);
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="centerid">🏥 Vaccination Center</label>
                        <select name="centerid" id="centerid" required class="form-select">
                            <option value="">Select a center...</option>
                            <?php
                            $result = mysqli_query($conn, "SELECT CenterId, Name FROM centers");
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . htmlspecialchars($row['CenterId']) . "'>" . htmlspecialchars($row['Name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="amount">💰 Payment Amount (BDT)</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required class="form-input" placeholder="Enter amount in Taka">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="method">💳 Payment Method</label>
                        <select name="method" id="method" required class="form-select">
                            <option value="">Choose payment method...</option>
                            <option value="Cash">💵 Cash</option>
                            <option value="Card">💳 Debit/Credit Card</option>
                            <optgroup label="📱 Mobile Banking">
                                <option value="Bkash">🔴 bKash</option>
                                <option value="Nagad">🟠 Nagad</option>
                            </optgroup>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        💸 Process Payment
                    </button>
                </form>
            </div>

            <div class="receipt-section">
                <?php if ($receipt) { ?>
                    <div class="receipt-container success-animation">
                        <div class="receipt-header">
                            <h2>🧾 Payment Receipt</h2>
                            <p style="color: rgba(255, 255, 255, 0.7);">Payment processed successfully</p>
                        </div>
                        
                        <div class="receipt-details">
                            <div class="receipt-row">
                                <span class="receipt-label">Transaction ID:</span>
                                <span class="receipt-value"><?php echo htmlspecialchars($receipt['TransactionId']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Payment ID:</span>
                                <span class="receipt-value">#<?php echo htmlspecialchars($receipt['PaymentId']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Child Name:</span>
                                <span class="receipt-value"><?php echo htmlspecialchars($receipt['ChildName']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Center:</span>
                                <span class="receipt-value"><?php echo htmlspecialchars($receipt['CenterName']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Payment Method:</span>
                                <span class="receipt-value"><?php echo htmlspecialchars($receipt['Method']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Date:</span>
                                <span class="receipt-value"><?php echo htmlspecialchars($receipt['Date']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Amount:</span>
                                <span class="receipt-value">৳<?php echo htmlspecialchars($receipt['Amount']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 2rem;">
                         <a href="receipt_pdf.php?id=<?php echo $receipt['PaymentId']; ?>" target="_blank" class="download-btn">
                            📥 Download PDF
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="receipt-container">
                        <div class="receipt-header">
                            <h2>💳 Ready to Pay?</h2>
                            <p style="color: rgba(255, 255, 255, 0.7);">Fill out the form to process your payment</p>
                        </div>
                        <div style="font-size: 4rem; opacity: 0.3; margin: 2rem 0;">🔒</div>
                        <p style="color: rgba(255, 255, 255, 0.6);">Your payment receipt will appear here after a successful transaction</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <script>
        // Add loading animation to submit button
        document.getElementById('paymentForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '💸 Processing Payment...';
        });

        // Add smooth focus transitions
        const formElements = document.querySelectorAll('.form-select, .form-input');
        formElements.forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>