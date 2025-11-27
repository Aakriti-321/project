<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "vehiclerentalsystem");
if ($conn->connect_error) die("Database connection failed");

$username = $_SESSION['username'];

// Handle new booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = "pending";
    $total_amount = 0;

    $conn->query("INSERT INTO bookings (user_name, vehicle_id, start_date, end_date, total_amount, status)
                  VALUES ('$username', '$vehicle_id', '$start_date', '$end_date', '$total_amount', '$status')");
    header("Location: booking.php");
    exit();
}

// Fetch bookings for current user
$query = "SELECT b.*, v.vehicle_name, v.model, v.price_per_day
          FROM bookings b
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id
          WHERE b.user_name = '$username'
          ORDER BY b.start_date DESC";

$bookings = $conn->query($query);

// Function to display Pay button
function payButton($status, $booking_id, $total_amount) {
    if ($status === 'confirmed') {
        return "
        <form action='pay_khalti.php' method='POST'>
            <input type='hidden' name='booking_id' value='$booking_id'>
            <input type='hidden' name='amount' value='$total_amount'>
            <button type='submit' style='background:#5a2ca0; color:white; padding:6px 12px; border:none; border-radius:5px; cursor:pointer;'>
                Pay with Khalti
            </button>
        </form>";
    }
    return "-";
}
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
    <h4>Welcome, <?= $username ?></h4>
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
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($bookings->num_rows > 0) {
            $sn = 1;
            while ($row = $bookings->fetch_assoc()) {
                $days = (strtotime($row['end_date']) - strtotime($row['start_date'])) / 86400 + 1;
                $total_amount = $row['price_per_day'] * $days;

                $status = strtolower($row['status']);
                $color = match($status) {
                    "pending" => "orange",
                    "confirmed" => "green",
                    "cancelled" => "red",
                    "completed" => "blue",
                    default => "black"
                };

                echo "<tr>
                    <td>$sn</td>
                    <td>{$row['vehicle_name']}</td>
                    <td>{$row['model']}</td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                    <td>NPR $total_amount</td>
                    <td><b style='color:$color'>".ucfirst($status)."</b></td>
                    <td>".payButton($status, $row['booking_id'], $total_amount)."</td>
                </tr>";
                $sn++;
            }
        } else {
            echo "<tr><td colspan='8'>No bookings found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>
