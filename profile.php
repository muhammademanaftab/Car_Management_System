<?php

function getUserReservations($userEmail) {
    $reservationStorage = new Storage(new JsonIO('reservations.json'));
    return $reservationStorage->findAll(['user_email' => $userEmail]);
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

// Ensure all necessary fields exist in the session user data
if (!isset($user['password']) || !isset($user['id'])) {
    die("User session data is incomplete. Please log out and log in again.");
}

// Handle profile data update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedName = $_POST['name'] ?? $user['fullname'];
    $updatedEmail = $_POST['email'] ?? $user['email'];
    $updatedPassword = $_POST['password'] ?? '';

    // Handle password update
    if (!empty($updatedPassword)) {
        $updatedPassword = $updatedPassword; // Store plain text password as per requirements
    } else {
        $updatedPassword = $user['password'];
    }

    // Update user data in session and save to users.json
    $user['fullname'] = $updatedName;
    $user['email'] = $updatedEmail;
    $user['password'] = $updatedPassword;

    // Save updated data to users.json
    $userStorage = new Storage(new JsonIO('users.json'));
    $userStorage->update((string)$user['id'], $user); // Cast ID to string

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
                    <button class="profile-btn">Welcome, <?php echo htmlspecialchars($user['fullname']); ?></button>
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
            <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h1>
            <div class="profile-details">
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <h2>Update Your Profile</h2>
            <form action="profile.php" method="POST">
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div>
                    <label for="password">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" id="password">
                </div>
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </main>
</body>
</html>
