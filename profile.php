<?php
session_start();
include 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, profile_image FROM users WHERE id = ? AND role = ?");
$stmt->bind_param("is", $user_id, $user_role);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$profile_image = $user['profile_image'] ?? 'default.png';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $profile_image = $user['profile_image']; // default to current

    // Upload profile image if provided
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/profile_images/" . $user_role . "/"; // role-based folder
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $user_role . "/" . $file_name;
        }
    }

    // Update DB
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ? AND role = ?");
    $stmt->bind_param("sssis", $name, $email, $profile_image, $user_id, $user_role);
    $stmt->execute();

    // Update session variables
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['profile_image'] = $profile_image;

    // Refresh page
    header("Location: dashboard.php?profile_updated=1");

    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Profile</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile-container h1 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
            margin-bottom: 15px;
        }

        .profile-upload {
            margin: 15px 0;
        }

        .profile-container form input,
        .profile-container form button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .profile-container form button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        .profile-container form button:hover {
            background-color: #2980b9;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="profile-container">
        <h1>Manage Profile</h1>
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">Profile updated successfully!</p>
        <?php endif; ?>

        <img src="uploads/profile_images/<?php echo htmlspecialchars($profile_image); ?>" class="profile-image"
            alt="Profile Picture">

        <form method="POST" enctype="multipart/form-data">
            <div class="profile-upload">
                <input type="file" name="profile_image" accept="image/*">
            </div>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            <button type="submit">Update Profile</button>
        </form>
    </div>

</body>

</html>