<?php
$conn = new mysqli("localhost","root","","vehiclerentalsystem");
$id = $_GET['id'];
$v = $conn->query("SELECT * FROM vehicles WHERE vehicle_id=$id")->fetch_assoc();
?>
<form action="vehicle_update.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="text" name="vehicle_name" value="<?php echo $v['vehicle_name']; ?>">
<input type="text" name="category_type" value="<?php echo $v['category_type']; ?>">
<input type="text" name="model" value="<?php echo $v['model']; ?>">
<input type="number" name="price_per_day" value="<?php echo $v['price_per_day']; ?>">
<input type="file" name="image">
<button type="submit">Update</button>
</form>
