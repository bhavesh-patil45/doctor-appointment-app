<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = (int) $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];

// ✅ Always fetch the latest profile image from DB
$stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_img = $stmt->get_result();
$row_img = $result_img->fetch_assoc();
$profile_image = $row_img['profile_image'] ?? 'default.png';

// --- For Doctors ---
if ($user_role === 'doctor') {
    // Get doctor availability
    $stmt = $conn->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ? ORDER BY available_date");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $availability = $stmt->get_result();

    // Get pending appointment requests
    $stmt = $conn->prepare("
        SELECT a.id, u.name AS patient_name, a.appointment_date, a.status
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ? AND a.status = 'pending'
        ORDER BY a.appointment_date
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pending_requests = $stmt->get_result();
}

// --- For Patients ---
if ($user_role === 'patient') {
    // Get available doctors
    $available_doctors = $conn->query("
        SELECT u.id, u.name, u.profile_image, da.available_date, da.available_time
        FROM users u
        JOIN doctor_availability da ON u.id = da.doctor_id
        WHERE u.role = 'doctor'
        ORDER BY da.available_date
    ");

    // Get upcoming appointments
    $stmt = $conn->prepare("
        SELECT a.id, u.name AS doctor_name, a.appointment_date, a.status
        FROM appointments a
        JOIN users u ON a.doctor_id = u.id
        WHERE a.patient_id = ? AND a.status = 'accepted'
        ORDER BY a.appointment_date
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $upcoming_appointments = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo ucfirst($user_role); ?> Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        body {
            background: url("assets/images/<?php echo $user_role === 'doctor' ? 'doctor-dashboard.jpg' : 'patient-dashboard.jpg'; ?>") no-repeat center center fixed;
            background-size: cover;
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #fff;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <?php
        // Fallback if file not found
        $img_path = "uploads/" . $profile_image;
        if (!file_exists($img_path) || empty($profile_image)) {
            $img_path = "uploads/default.png";
        }
        ?>
        <img src="uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" width="100">


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


    <!-- Main Content -->
    <div class="main-content">
        <h1>Welcome, <?php echo ($user_role === 'doctor' ? 'Dr. ' : '') . htmlspecialchars($user_name); ?></h1>
        <h2>Upcoming Appointments</h2>

        <?php
        if ($user_role === 'doctor') {
            $result = $pending_requests;
        } else {
            $result = $upcoming_appointments;
        }

        if ($result && $result->num_rows > 0):
            ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th><?php echo $user_role === 'doctor' ? 'Patient' : 'Doctor'; ?></th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['appointment_date']; ?></td>
                        <td><?php echo htmlspecialchars($user_role === 'doctor' ? $row['patient_name'] : $row['doctor_name']); ?>
                        </td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No upcoming appointments.</p>
        <?php endif; ?>

    </div>

</body>

</html>