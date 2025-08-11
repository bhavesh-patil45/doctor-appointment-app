<?php
session_start();

// Protect page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$patient_id = $_SESSION['user_id'];

// Get patient's upcoming appointments
$stmt = $conn->prepare("
    SELECT a.id, a.appointment_date, a.appointment_time, u.name AS doctor_name, a.status
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date, a.appointment_time
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="sidebar">
        <h2>Patient Panel</h2>
        <a href="patient-dashboard.php">Home</a>
        <a href="appointments.php">Appointments</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <h2>Upcoming Appointments</h2>
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                        <td><?= $row['status'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No upcoming appointments.</p>
        <?php endif; ?>
    </div>
</body>

</html>