<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($email === '' || $pass === '' || ($role !== 'doctor' && $role !== 'patient')) {
        $error = 'Please fill all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = ?");
        if (!$stmt) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param("ss", $email, $role);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $name, $hashed, $dbRole);
                $stmt->fetch();
                if (password_verify($pass, $hashed)) {
                    // Login success
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $dbRole;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = 'Invalid password.';
                }
            } else {
                $error = 'No account found with that email and role.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="form-container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />

            <select name="role" required>
                <option value="">Login as</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>

            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register</a></p>
            <?php if (!empty($error)): ?>
                <p style="color:red; text-align:center;"><?php echo $error; ?></p>
            <?php endif; ?>

        </form>
    </div>

</body>

</html>