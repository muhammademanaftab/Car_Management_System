<?php
session_start();
require_once 'storage.php'; // Include the necessary storage file

// Initialize the JSON file handler for users
$userStorage = new Storage(new JsonIO('users.json'));

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Search for user by email
    $user = $userStorage->findOne(['email' => $email]);

    // Check if the user exists and passwords match
    if ($user && password_verify($password, $user['password'])) {
        // Store the user information in the session
        $_SESSION['user'] = [
            'name' => $user['fullname'],  // Make sure the name field exists in the user data
            'email' => $user['email'],
            'password' => $user['password'],
            // Add other necessary user details here
        ];
        header('Location: homepage.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - iKarRental</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <header>
    <div class="logo">iKarRental</div>
    <div class="nav">
      <a href="login.php" class="nav-link">Login</a>
      <a href="registration.php" class="nav-button">Registration</a>
    </div>
  </header>

  <main>
    <div class="login-container">
      <h1>Login</h1>

      <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <label for="email">Email address</label>
        <input type="email" name="email" id="email" placeholder="Email" required>
        
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
        
        <button type="submit">Login</button>
      </form>
    </div>
  </main>
</body>
</html>
