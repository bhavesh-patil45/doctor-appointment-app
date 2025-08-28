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
        // Check if account already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = ?");
        $check->bind_param("ss", $email, $role);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Account already exists for this email and role.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            // --- Handle Profile Image Upload ---
            $default_image = 'default.png';
            $profile_image = $default_image;

            if (!empty($_FILES['profile_image']['name'])) {
                $target_dir = "uploads/";
                $file_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
                $target_file = $target_dir . $file_name;

                // Check if uploads folder exists
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                // Validate image type
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowed_types)) {
                    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                        $profile_image = $file_name;
                    } else {
                        $error = "Error uploading profile image.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
                }
            }

            if (empty($error)) {
                $ins = $conn->prepare("INSERT INTO users (name, email, password, role, profile_image) VALUES (?, ?, ?, ?, ?)");
                $ins->bind_param("sssss", $name, $email, $hash, $role, $profile_image);

                if ($ins->execute()) {
                    $_SESSION['user_id'] = $ins->insert_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $role;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = 'Registration failed: ' . $ins->error;
                }
            }
        }
        $check->close();
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
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full Name" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <select name="role" required>
                <option value="">Register as</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>
            <input type="file" name="profile_image" accept="image/*" />
            <button type="submit">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
            <?php if (!empty($error)): ?>
                <p style="color:red; text-align:center;"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>