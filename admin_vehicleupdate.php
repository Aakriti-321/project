<?php
$conn = new mysqli("localhost","root","","vehiclerentalsystem");

$id = $_POST['id'];
$name = $_POST['vehicle_name'];
$cat = $_POST['category_type'];
$model = $_POST['model'];
$price = $_POST['price_per_day'];

if($_FILES['image']['name'] != ""){
$img = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];
move_uploaded_file($tmp,"uploads/".$img);
$conn->query("UPDATE vehicles SET vehicle_name='$name',category_type='$cat',model='$model',price_per_day='$price',image='$img' WHERE vehicle_id=$id");
}
else{
$conn->query("UPDATE vehicles SET vehicle_name='$name',category_type='$cat',model='$model',price_per_day='$price' WHERE vehicle_id=$id");
}

header("Location: admin_dashboard.php");
