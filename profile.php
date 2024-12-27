<?php

function getUserReservations($userEmail) {
    $reservationStorage = new Storage(new JsonIO('reservations.json'));
    $userReservations = $reservationStorage->findAll(['user_email' => $userEmail]);
    return $userReservations;
}

session_start();
require_once 'storage.php'; // Include the Storage class

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$is_logged_in = isset($_SESSION['user']); // Check if user is logged in
$user = $_SESSION['user'];

// Ensure profile picture has a default value if null or not set
$profilePicture = $user['profile_picture'] ?? 'uploads/default_profile_picture.png';
if (empty($profilePicture)) {
    $profilePicture = 'uploads/default_profile_picture.png';
}

// Handle profile picture upload and data update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedName = $_POST['name'] ?? $user['name'];
    $updatedEmail = $_POST['email'] ?? $user['email'];
    $updatedPassword = $_POST['password'] ?? '';
    $updatedProfilePicture = $profilePicture;

    // If a new profile picture is uploaded, handle it
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $newFileName = uniqid() . '_' . $fileName;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $filePath)) {
            $updatedProfilePicture = $filePath;
        }
    }

    // Handle password update
    if (!empty($updatedPassword)) {
        $updatedPassword = password_hash($updatedPassword, PASSWORD_BCRYPT);
    } else {
        $updatedPassword = $user['password'];
    }

    // Update user data in session and save to users.json
    $user['name'] = $updatedName;
    $user['email'] = $updatedEmail;
    $user['password'] = $updatedPassword;
    $user['profile_picture'] = $updatedProfilePicture;

    // Save updated data to users.json
    $userStorage = new Storage(new JsonIO('users.json'));
    $userStorage->update($user['email'], $user);

    $_SESSION['user'] = $user;

    echo "<script>alert('Profile updated successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - iKarRental</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <header>
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
            <?php if ($is_logged_in): ?>
                <div class="profile-dropdown">
                    <button class="profile-btn">Welcome, <?php echo htmlspecialchars($user['name']); ?></button>
                    <div class="dropdown-content">
                        <a href="profile.php">Profile Settings</a>
                        <a href="reservations.php">My Reservations</a>
                    </div>
                </div>
                <a href="logout.php" class="button">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="registration.php" class="button">Registration</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="profile-container">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
            <div class="profile-details">
                <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" class="profile-picture">
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <h2>Update Your Profile</h2>
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div>
                    <label for="password">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" id="password">
                </div>
                <div>
                    <label for="profile_picture">Upload Profile Picture</label>
                    <input type="file" name="profile_picture" id="profile_picture">
                </div>
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </main>
</body>
</html>
