<?php
session_start();
include 'db.php'; // Make sure this file exists in your root directory

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $update = "UPDATE users SET name='$name', email='$email' WHERE id=$userId";
    mysqli_query($conn, $update);

    $_SESSION['user_name'] = $name;
    header("Location: profile.php");
    exit();
}

$query = "SELECT * FROM users WHERE id=$userId";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <button class="toggle-btn" onclick="toggleSidebar()">☰ Menu</button>

    <div class="sidebar" id="sidebar">
        <a href="dashboard-patient.php">Home</a>
        <a href="appointments.php">Appointments</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Edit Profile</h1>
        <form method="post">
            <label>Name:</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

            <button type="submit">Update</button>
        </form>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }
    </script>
</body>

</html>