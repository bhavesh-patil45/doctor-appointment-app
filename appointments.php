<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure role is set in session
if (!isset($_SESSION['user_role'])) {
    // You can either redirect or set a default role
    // Here, redirecting to login to avoid broken queries
    header("Location: login.php");
    exit();
}

include(__DIR__ . '/db.php'); // DB connection
$userId = intval($_SESSION['user_id']);
$role = $_SESSION['user_role'];

// Select appointments depending on role
if ($role === 'doctor') {
    $query = "SELECT * FROM appointments WHERE doctor_id = $userId ORDER BY appointment_date";
} else {
    $query = "SELECT * FROM appointments WHERE patient_id = $userId ORDER BY appointment_date";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Appointments</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="sidebar">
        <h2><?php echo ucfirst($role); ?> Panel</h2>
        <a href="dashboard-<?php echo $role; ?>.php">Home</a>
        <a href="appointments.php" class="active">Appointments</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Appointments</h1>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <?php if ($role === 'doctor')
                        echo "<th>Patient ID</th>";
                    else
                        echo "<th>Doctor ID</th>"; ?>
                    <th>Status</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td><?= htmlspecialchars($role === 'doctor' ? $row['patient_id'] : $row['doctor_id']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No appointments found.</p>
        <?php endif; ?>
    </div>
</body>

</html>