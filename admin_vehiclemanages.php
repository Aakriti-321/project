<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header("Location: index.html");
    exit();
}

$conn = new mysqli("localhost","root","","vehiclerentalsystem");
if($conn->connect_error) die("Connection Failed");

$admin_email = $_SESSION['admin'];

// Handle Add Vehicle
if(isset($_POST['add_vehicle'])) {
    $name = $_POST['vehicle_name'];
    $category = $_POST['category_type'];
    $model = $_POST['model'];
    $price = $_POST['price_per_day'];
    $status = 'Available';

    // Handle image upload
    $img_name = $_FILES['image']['name'];
    $img_tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($img_tmp, "uploads/".$img_name);

    $conn->query("INSERT INTO vehicles (vehicle_name, category_type, model, price_per_day, image, status) 
                  VALUES ('$name','$category','$model','$price','$img_name','$status')");
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM vehicles WHERE vehicle_id=$id");
}

// Handle Update
if(isset($_POST['update_vehicle'])) {
    $id = $_POST['vehicle_id'];
    $name = $_POST['vehicle_name'];
    $category = $_POST['category_type'];
    $model = $_POST['model'];
    $price = $_POST['price_per_day'];

    if($_FILES['image']['name'] != "") {
        $img_name = $_FILES['image']['name'];
        $img_tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($img_tmp, "uploads/".$img_name);
        $conn->query("UPDATE vehicles SET vehicle_name='$name', category_type='$category', model='$model', price_per_day='$price', image='$img_name' WHERE vehicle_id=$id");
    } else {
        $conn->query("UPDATE vehicles SET vehicle_name='$name', category_type='$category', model='$model', price_per_day='$price' WHERE vehicle_id=$id");
    }
}

// Fetch vehicles
$vehicles = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_id DESC");

// Fetch vehicle for update
$edit_vehicle = null;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM vehicles WHERE vehicle_id=$id");
    $edit_vehicle = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Vehicles</title>
<link rel="stylesheet" href="admin_vehiclemanages.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
</head>
<body>

<header>
    <img src="logo1.jpg" alt="Logo">
    <h4>Easy Ride Admin</h4>
</header>

<div class="flex">
    <div class="sidebar">
        <img src="https://t4.ftcdn.net/jpg/16/09/59/37/360_F_1609593795_Ae1PPBgGSiy2tKw4GWXeXJtBTQn3dWpn.jpg" alt="Profile">
        <h4>Welcome, <?= $admin_email ?></h4>
        <a href="admin_dashboard.php" class="menu"><span class="material-icons-sharp">dashboard</span> Dashboard</a>
        <a href="admin_manages.php" class="menu"><span class="material-icons-sharp">people</span>Bookings</a>
        <a href="admin_vehiclemanages.php" class="menu"><span class="material-icons-sharp">directions_car</span> Vehicles</a>
        <a href="logout.php" class="menu"><span class="material-icons-sharp">logout</span> Logout</a>
    </div>

    <div class="main">
        <h2>Add Vehicle</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="vehicle_name" placeholder="Vehicle Name" required>
            <input type="text" name="category_type" placeholder="Category Type" required>
            <input type="text" name="model" placeholder="Model" required>
            <input type="number" name="price_per_day" placeholder="Price Per Day" required>
            <input type="file" name="image" required> <br>
            <button type="submit" name="add_vehicle">Add Vehicle</button><br><br>

        </form>

        <?php if($edit_vehicle): ?>
        <h2>Update Vehicle</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="vehicle_id" value="<?= $edit_vehicle['vehicle_id'] ?>">
            <input type="text" name="vehicle_name" value="<?= $edit_vehicle['vehicle_name'] ?>" required>
            <input type="text" name="category_type" value="<?= $edit_vehicle['category_type'] ?>" required>
            <input type="text" name="model" value="<?= $edit_vehicle['model'] ?>" required>
            <input type="number" name="price_per_day" value="<?= $edit_vehicle['price_per_day'] ?>" required>
            <input type="file" name="image"><br>
            <button type="submit" name="update_vehicle">Update Vehicle</button><br><br>
        </form>
        <?php endif; ?>

        <h2>Vehicles List</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Model</th>
                <th>Price</th>
                <th>Image</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while($v = $vehicles->fetch_assoc()): ?>
            <tr>
                <td><?= $v['vehicle_id'] ?></td>
                <td><?= $v['vehicle_name'] ?></td>
                <td><?= $v['category_type'] ?></td>
                <td><?= $v['model'] ?></td>
                <td><?= $v['price_per_day'] ?></td>
                <td><img src="uploads/<?= $v['image'] ?>" width="70"></td>
                <td><?= $v['status'] ?></td>
                <td>
                    <a href="?edit=<?= $v['vehicle_id'] ?>" class="button">Edit</a>
                    <a href="?delete=<?= $v['vehicle_id'] ?>" class="button button-red" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
