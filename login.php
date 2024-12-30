<?php
session_start();
require_once 'storage.php'; // Include the necessary storage file

// Initialize the JSON file handler for users
$userStorage = new Storage(new JsonIO('users.json'));

// Initialize variables
$email = $password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    // If no errors, process login
    if (empty($errors)) {
        // Check for admin credentials
        if ($email === 'admin@ikarrental.hu' && $password === 'admin') {
            $_SESSION['admin'] = true;
            header('Location: admin_profile.php');  // Admin Profile Page
            exit();
        }

        // Search for user by email if not admin
        $user = $userStorage->findOne(['email' => $email]);

        // Check if the user exists and passwords match (plain text comparison)
        if ($user && $password === $user['password']) { // Plain text comparison
            // Store the user information in the session
            $_SESSION['user'] = [
                'fullname' => $user['fullname'],  // Ensure the name field matches the updated structure
                'email' => $user['email'],
            ];
            header('Location: homepage.php');  // User's Homepage
            exit;
        } else {
            $errors['general'] = 'Invalid email or password.';
        }
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
    <div class="logo"><a href="homepage.php">iKarRental</a></div>
    <div class="nav">
      <a href="login.php" class="nav-link">Login</a>
      <a href="registration.php" class="nav-button">Registration</a>
    </div>
  </header>

  <main>
    <div class="login-container">
      <h1>Login</h1>

      <?php if (!empty($errors['general'])): ?>
        <div class="error"><?php echo htmlspecialchars($errors['general']); ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <label for="email">Email address</label>
        <input type="email" name="email" id="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>">
        <?php if (isset($errors['email'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['email']); ?></div>
        <?php endif; ?>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password">
        <?php if (isset($errors['password'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['password']); ?></div>
        <?php endif; ?>

        <button type="submit">Login</button>
      </form>
    </div>
  </main>
</body>
</html>
