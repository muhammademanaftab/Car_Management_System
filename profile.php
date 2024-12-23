<?php

function getUserReservations($userEmail) {
    // Assuming user reservations are stored in 'reservations.json'
    $reservationStorage = new Storage(new JsonIO('reservations.json'));

    // Fetch all reservations for the logged-in user
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

// Get the logged-in user data
$user = $_SESSION['user'];

// Handle profile picture upload and data update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedName = $_POST['name'] ?? $user['name'];
    $updatedEmail = $_POST['email'] ?? $user['email'];
    $updatedPassword = $_POST['password'] ?? '';
    $updatedProfilePicture = $user['profile_picture']; // Default to the existing profile picture

    // If a new profile picture is uploaded, handle it
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }
        $newFileName = uniqid() . '_' . $fileName;
        $filePath = $uploadDir . $newFileName;

        // Move uploaded file
        if (move_uploaded_file($fileTmpPath, $filePath)) {
            $updatedProfilePicture = $filePath;
        }
    }

    // Handle password update (if provided)
    if (!empty($updatedPassword)) {
        $updatedPassword = password_hash($updatedPassword, PASSWORD_BCRYPT); // Hash the password
    } else {
        $updatedPassword = $user['password']; // Retain old password if not updated
    }

    // Update user data in session and save to users.json
    $user['name'] = $updatedName;
    $user['email'] = $updatedEmail;
    $user['password'] = $updatedPassword;
    $user['profile_picture'] = $updatedProfilePicture;

    // Save the updated user data back to users.json
    $userStorage = new Storage(new JsonIO('users.json'));
    $userStorage->update($user['email'], $user);

    // Update session data
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
    <div class="logo">iKarRental</div>
    <div class="nav">
      <a href="logout.php" class="nav-button">Logout</a>
    </div>
  </header>

  <main>
    <div class="profile-container">
      <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
      <div class="profile-details">
        <!-- Display Profile Picture -->
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
      </div>

      <!-- Profile Update Form -->
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
