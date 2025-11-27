<?php
$conn = new mysqli("localhost","root","","vehiclerentalsystem");
$id = $_GET['id'];
$conn->query("DELETE FROM vehicles WHERE vehicle_id=$id");
header("Location: admin_dashboard.php");
