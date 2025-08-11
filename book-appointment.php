<?php
session_start();
if ($_SESSION['user_role'] !== 'patient') {
    header("Location: dashboard.php");
    exit();
}
include 'db.php';

// Fetch all doctors and availability
$availabilities = $conn->query("
    SELECT a.id AS availability_id, u.name AS doctor_name, a.available_date, a.available_time
    FROM availability a
    JOIN users u ON a.doctor_id = u.id
    ORDER BY a.available_date, a.available_time
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $availability_id = (int) $_POST['availability_id'];
    $patient_id = $_SESSION['user_id'];

    // Step 1: Check if slot exists
    $check = $conn->prepare("SELECT id FROM availability WHERE id = ?");
    $check->bind_param("i", $availability_id);
    $check->execute();
    $resultCheck = $check->get_result();

    if ($resultCheck->num_rows === 0) {
        $error = "Selected slot is no longer available.";
    } else {
        // Step 2: Prevent double booking
        $checkSlot = $conn->prepare("SELECT id FROM appointments WHERE availability_id = ?");
        $checkSlot->bind_param("i", $availability_id);
        $checkSlot->execute();
        $resultSlot = $checkSlot->get_result();

        if ($resultSlot->num_rows > 0) {
            $error = "This slot is already booked. Please choose another.";
        } else {
            // Step 3: Insert appointment
            $stmt = $conn->prepare("
                INSERT INTO appointments (availability_id, patient_id, status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->bind_param("ii", $availability_id, $patient_id);

            if ($stmt->execute()) {
                $success = "Appointment booked successfully!";
            } else {
                $error = "Error booking appointment: " . $conn->error;
            }
        }
    }
}
?>
<link rel="stylesheet" href="assets/css/forms.css">

<div class="container">
    <h2>Book Appointment</h2>
    <div class="page-header">
        <img src="assets/images/booking.jpg" alt="Book Appointment" style="max-width:100%; border-radius:10px;">
    </div>

    <?php if (!empty($success))
        echo "<p class='success'>$success</p>"; ?>
    <?php if (!empty($error))
        echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label for="availability_id">Select Doctor & Time</label>
        <select name="availability_id" required>
            <option value="">-- Choose Slot --</option>
            <?php while ($row = $availabilities->fetch_assoc()): ?>
                <option value="<?= $row['availability_id'] ?>">
                    <?= htmlspecialchars($row['doctor_name']) ?> -
                    <?= $row['available_date'] ?> at <?= $row['available_time'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Book Now</button>
    </form>
</div>