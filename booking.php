<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "vehiclerentalsystem");
if ($conn->connect_error) die("Database connection failed");

$username = $_SESSION['username'];

// eSewa config
$secretKey = "8gBm/:&EnhH.1/q"; 
$product_code = "EPAYTEST";
$signed_field_names = "total_amount,transaction_uuid,product_code";

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Only allow booking if start_date is today
    $today = date('Y-m-d');
    if ($start_date != $today) {
        echo "<script>alert('You can only book for today!'); window.location='booking.php';</script>";
        exit();
    }

    $result = $conn->query("SELECT price_per_day FROM vehicles WHERE vehicle_id = $vehicle_id");
    $row = $result->fetch_assoc();
    $price_per_day = $row['price_per_day'];
    $days = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    $total_amount = $price_per_day * $days;

    // Insert booking
    $conn->query("INSERT INTO bookings (user_name, vehicle_id, start_date, end_date, total_amount)
                  VALUES ('$username', '$vehicle_id', '$start_date', '$end_date', '$total_amount')");
    $booking_id = $conn->insert_id;

    // Generate transaction UUID
    $transaction_uuid = $booking_id . '-' . time();
    $conn->query("UPDATE bookings SET transaction_uuid='$transaction_uuid' WHERE booking_id=$booking_id");

    header("Location: booking.php");
    exit();
}

// Fetch user's bookings
$query = "SELECT b.*, v.vehicle_name, v.model, v.price_per_day
          FROM bookings b
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id
          WHERE b.user_name = '$username'
          ORDER BY b.start_date DESC";
$bookings = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="booking.css">
</head>
<body>

<header>
    <img src="logo1.jpg" alt="Logo">
    <h4>EasyRide</h4>
</header>

<div class="flex">
<div class="sidebar">
    <img src="https://t4.ftcdn.net/jpg/16/09/59/37/360_F_1609593795_Ae1PPBgGSiy2tKw4GWXeXJtBTQn3dWpn.jpg" alt="Profile">
    <h4>Welcome, <?= htmlspecialchars($username) ?></h4>
    <a href="dashboard.php" class="menu"><span class="material-icons">dashboard</span> Dashboard</a>
    <a href="rent.php" class="menu"><span class="material-icons">directions_car</span> Rent Now</a>
    <a href="booking.php" class="menu"><span class="material-icons">calendar_today</span> My Booking</a>
    <a href="logout.php" class="menu"><span class="material-icons">logout</span> Logout</a>
</div>

<div class="main">
    <h2>My Bookings</h2>
    <table width="100%" cellpadding="10">
        <thead>
            <tr>
                <th>S.N</th>
                <th>Vehicle Name</th>
                <th>Model</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Amount</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($bookings->num_rows > 0):
            $sn = 1;
            while ($row = $bookings->fetch_assoc()):
                $days = (strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400 + 1;
                $total_amount = $row['price_per_day'] * $days;
                $total_amount_str = number_format($total_amount, 2, '.', '');

                $transaction_uuid = $row['transaction_uuid']; // use fixed transaction_uuid
                $signed_fields = [
                    'total_amount' => $total_amount_str,
                    'transaction_uuid' => $transaction_uuid,
                    'product_code' => $product_code
                ];
                $fields_order = explode(',', $signed_field_names);
                $data_to_sign = [];
                foreach ($fields_order as $field) $data_to_sign[] = $field . '=' . $signed_fields[$field];
                $data_string = implode(',', $data_to_sign);
                $hash = hash_hmac('sha256', $data_string, $secretKey, true);
                $signature = base64_encode($hash);
        ?>
            <tr>
                <td><?= $sn ?></td>
                <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
                <td><?= htmlspecialchars($row['model']) ?></td>
                <td><?= $row['start_date'] ?></td>
                <td><?= $row['end_date'] ?></td>
                <td>NPR <?= $total_amount_str ?></td>
                <td>
                    <form method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
                        <input type="hidden" name="amount" value="<?= $total_amount_str ?>">
                        <input type="hidden" name="tax_amount" value="0">
                        <input type="hidden" name="total_amount" value="<?= $total_amount_str ?>">
                        <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
                        <input type="hidden" name="product_code" value="<?= $product_code ?>">
                        <input type="hidden" name="product_service_charge" value="0">
                        <input type="hidden" name="product_delivery_charge" value="0">
                        <input type="hidden" name="success_url" value="http://localhost/vehiclerentalsystem/success.php">
                        <input type="hidden" name="failure_url" value="http://localhost/vehiclerentalsystem/failure.php">
                        <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>">
                        <input type="hidden" name="signature" value="<?= $signature ?>">
                        <input type="submit" value="Pay with eSewa">
                    </form>
                </td>
            </tr>
        <?php
                $sn++;
            endwhile;
        else:
            echo "<tr><td colspan='7'>No bookings found</td></tr>";
        endif;
        ?>
        </tbody>
    </table>
</div>
</div>
</body>
</html>
