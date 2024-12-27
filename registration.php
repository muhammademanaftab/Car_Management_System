<?php
include('storage.php');

// Initialize variables
$errors = [];
$success = false;
$fullname = $email = $password = $confirm_password = '';

// Create an instance of the JsonIO for users storage
$jsonStorage = new JsonIO('users.json');
$userStorage = new Storage($jsonStorage);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Retrieve form data
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    // Validation for full name
    if (empty($fullname)) {
        $errors['fullname'] = "Full name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $fullname)) {
        $errors['fullname'] = "Only letters and white space allowed.";
    }

    // Validation for email address
    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if the email already exists
        $existingUser = $userStorage->findOne(['email' => $email]);
        if ($existingUser) {
            $errors['email'] = "This email is already registered.";
        }
    }

    // Validation for password
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    }

    // Validation for password confirmation
    if (empty($confirm_password)) {
        $errors['confirm-password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm-password'] = "Passwords do not match.";
    }

    // If no errors, proceed to store the user
    if (empty($errors)) {
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Save the user in the storage (users.json)
        $newUser = [
            'fullname' => $fullname,
            'email' => $email,
            'password' => $hashedPassword,
            'status' => 'user'  // Default user status
        ];

        $userStorage->add($newUser);  // Save the user in the JSON storage
        $success = true;  // Registration success
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - iKarRental</title>
    <link rel="stylesheet" href="registration.css">
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
        <div class="registration-container">
            <h1>Registration</h1>

            <?php if ($success): ?>
                <div style="color: green; font-weight: bold;">Registration successful! You can now log in.</div>
                <a href="login.php">Login here</a> <!-- Link to login page -->
            <?php endif; ?>

            <form action="registration.php" method="POST">
                <label for="fullname">Full name</label>
                <input type="text" name="fullname" id="fullname" placeholder="Jakab Gips" value="<?php echo htmlspecialchars($fullname); ?>" >
                <?php if (isset($errors['fullname'])) { ?>
                    <span style="color: red;"><?php echo $errors['fullname']; ?></span>
                <?php } ?>
                
                <label for="email">Email address</label>
                <input type="email" name="email" id="email" placeholder="jakab.gipsz@ikarrental.net" value="<?php echo htmlspecialchars($email); ?>" >
                <?php if (isset($errors['email'])) { ?>
                    <span style="color: red;"><?php echo $errors['email']; ?></span>
                <?php } ?>
                
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="********" >
                <?php if (isset($errors['password'])) { ?>
                    <span style="color: red;"><?php echo $errors['password']; ?></span>
                <?php } ?>
                
                <label for="confirm-password">Password again</label>
                <input type="password" name="confirm-password" id="confirm-password" placeholder="********" >
                <?php if (isset($errors['confirm-password'])) { ?>
                    <span style="color: red;"><?php echo $errors['confirm-password']; ?></span>
                <?php } ?>
                
                <button type="submit">Register</button>
            </form>
        </div>
    </main>
</body>
</html>
                    