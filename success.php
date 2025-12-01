<?php
session_start();
$conn = new mysqli("localhost", "root", "", "vehiclerentalsystem");
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.html");
    exit();
}

// Get username from session email
$email = $_SESSION['email'];
$res = $conn->query("SELECT username FROM users WHERE email='$email'");
$user_name = ($res->num_rows > 0) ? $res->fetch_assoc()['username'] : 'User';

// Get data from GET
$data = $_GET['data'] ?? '';
$paymentInfo = [];
$booking_id = 0;

if ($data) {
    $decoded = base64_decode($data);
    $paymentInfo = json_decode($decoded, true);

    if ($paymentInfo) {
        // Try to get booking_id either from GET or from transaction_uuid
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        if ($booking_id === 0 && !empty($paymentInfo['transaction_uuid'])) {
            $parts = explode('-', $paymentInfo['transaction_uuid']);
            $booking_id = intval($parts[0]);
        }

        if ($booking_id > 0) {
            $amount = floatval($paymentInfo['total_amount'] ?? 0);
            $status = 'completed';
            $payment_date = date('Y-m-d H:i:s');

            // Insert payment
         $stmt = $conn->prepare("INSERT INTO payments (booking_id, user_name, amount, status, payment_date,transaction_uuid) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isds", $booking_id, $user_name, $amount, $status);
$stmt->execute();
            if ($stmt->execute()) {
                // Update booking status
                $update = $conn->prepare("UPDATE bookings SET status='completed' WHERE booking_id=?");
                $update->bind_param("i", $booking_id);
                $update->execute();
                $update->close();
            } else {
                echo "Error inserting payment: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Booking ID not found!";
        }
    } else {
        echo "Invalid payment data!";
    }
} else {
    echo "No payment data received!";
}
?>

<!DOCTYPE html>

<html>
<head>
    <title>Payment Success</title>
</head>
<body>
<h2>Payment Successful!</h2>
<?php if (!empty($paymentInfo)): ?>
    <p>Transaction UUID: <?= htmlspecialchars($paymentInfo['transaction_uuid'] ?? '') ?></p>
    <p>Status: <?= htmlspecialchars($status ?? '') ?></p>
    <p>Amount: NPR <?= htmlspecialchars($amount ?? 0) ?></p>
<?php else: ?>
    <p>No payment information received.</p>
<?php endif; ?>
<a href="booking.php">Back to Bookings</a>
</body>
</html>
