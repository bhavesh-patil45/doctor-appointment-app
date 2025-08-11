<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($name === '' || $email === '' || $pass === '' || ($role !== 'doctor' && $role !== 'patient')) {
        $error = 'Please fill all fields correctly.';
    } else {
        // check existing
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = ?");
        $check->bind_param("ss", $email, $role);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = 'Account already exists for this email and role.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $ins->bind_param("ssss", $name, $email, $hash, $role);
            if ($ins->execute()) {
                // set session and redirect
                $_SESSION['user_id'] = $ins->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="form-container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <input type="text" name="name" placeholder="Full Name" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />

            <select name="role" required>
                <option value="">Register as</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>

            <button type="submit">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
            <?php if (!empty($error)): ?>
                <p style="color:red; text-align:center;"><?php echo $error; ?></p>
            <?php endif; ?>

        </form>
    </div>

</body>

</html>