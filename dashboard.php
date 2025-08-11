<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

include 'db.php'; // include first so $conn exists

$user_id = (int) $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];

// Query based on role
if ($user_role === 'doctor') {
    $sql = "
        SELECT a.id, a.appointment_date, a.appointment_time, a.status,
               u.name AS patient_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date, a.appointment_time
    ";
} else {
    $sql = "
        SELECT a.id, a.appointment_date, a.appointment_time, a.status,
               u.name AS doctor_name
        FROM appointments a
        JOIN users u ON a.doctor_id = u.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date, a.appointment_time
    ";
}



$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo ucfirst($user_role); ?> Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>

    <div class="dashboard-banner">
        <?php if ($user_role === 'doctor'): ?>
            <img src="assets/images/doctor-dashboard.jpg" alt="Doctor Dashboard">
        <?php else: ?>
            <img src="assets/images/patient-dashboard.jpg" alt="Patient Dashboard">
        <?php endif; ?>
    </div>

    <div class="sidebar">
        <h2><?php echo ucfirst($user_role); ?> Panel</h2>
        <a href="dashboard.php">Dashboard</a>

        <?php if ($user_role === 'doctor'): ?>
            <a href="add-availability.php">Add Availability</a>
        <?php else: ?>
            <a href="book-appointment.php">Book Appointment</a>
        <?php endif; ?>

        <a href="profile.php">Manage Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo ($user_role === 'doctor' ? 'Dr. ' : '') . htmlspecialchars($user_name); ?></h1>
        <h2>Upcoming Appointments</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th><?php echo $user_role === 'doctor' ? 'Patient' : 'Doctor'; ?></th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= htmlspecialchars($user_role === 'doctor' ? $row['patient_name'] : $row['doctor_name']) ?></td>
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