<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Access control: doctor only
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'doctor') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$doctor_id = (int) $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['available_date'] ?? '');
    $time = trim($_POST['available_time'] ?? '');

    if ($date === '' || $time === '') {
        $error = 'Please select both a date and a time.';
    } else {
        $dobj = DateTime::createFromFormat('Y-m-d', $date);
        $tobj = DateTime::createFromFormat('H:i', $time);

        if (!$dobj || $dobj->format('Y-m-d') !== $date) {
            $error = 'Invalid date format.';
        } elseif (!$tobj || $tobj->format('H:i') !== $time) {
            $error = 'Invalid time format.';
        } else {
            // Step 1: Check for duplicate slot
            $check = $conn->prepare("
                SELECT id FROM availability 
                WHERE doctor_id = ? AND available_date = ? AND available_time = ?
            ");
            $check->bind_param("iss", $doctor_id, $date, $time);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $error = "You already have this time slot available.";
            } else {
                // Step 2: Insert availability
                $stmt = $conn->prepare("
                    INSERT INTO availability (doctor_id, available_date, available_time) 
                    VALUES (?, ?, ?)
                ");
                if ($stmt) {
                    $stmt->bind_param("iss", $doctor_id, $date, $time);
                    if ($stmt->execute()) {
                        $success = "Availability added for $date at $time.";
                    } else {
                        $error = 'Database error: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = 'Database error: ' . $conn->error;
                }
            }
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Add Availability</title>
    <link rel="stylesheet" href="assets/css/forms.css">
</head>

<body>
    <div class="page-header">
        <img src="assets/images/booking.jpg" alt="Add Availability" style="max-width:100%; border-radius:10px;">
    </div>

    <div class="container">
        <h2>Add Availability</h2>

        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="available_date">Select Date</label>
            <input id="available_date" name="available_date" type="date" required>

            <label for="available_time">Select Time</label>
            <input id="available_time" name="available_time" type="time" required>

            <button type="submit">Save Availability</button>
        </form>
    </div>
</body>

</html>