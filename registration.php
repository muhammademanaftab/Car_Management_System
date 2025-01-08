<?php
include('storage.php');

$errors = [];
$success = false;
$fullname = $email = $password = $confirm_password = '';

$jsonStorage = new JsonIO('users.json');
$userStorage = new Storage($jsonStorage);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    if (empty($fullname)) {
        $errors['fullname'] = "Full name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $fullname)) {
        $errors['fullname'] = "Full name must contain only letters and spaces.";
    }

    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        $existingUser = $userStorage->findOne(['email' => $email]);
        if ($existingUser) {
            $errors['email'] = "This email is already registered.";
        }
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if ($password !== $confirm_password) {
        $errors['confirm-password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $newUser = [
            'fullname' => $fullname,
            'email' => $email,
            'password' => $password,
            'status' => 'user'
        ];

        $userStorage->add($newUser);
        $success = true;
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
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
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
                <a href="login.php">Login here</a>
            <?php endif; ?>

            <form action="registration.php" method="POST">
                <label for="fullname">Full name</label>
                <input type="text" name="fullname" id="fullname" placeholder="Your Name" value="<?php echo ($fullname); ?>">
                <?php if (isset($errors['fullname'])) { ?>
                    <span style="color: red;"><?php echo $errors['fullname']; ?></span>
                <?php } ?>

                <label for="email">Email address</label>
                <input type="email" name="email" id="email" placeholder="email@example.com" value="<?php echo ($email); ?>">
                <?php if (isset($errors['email'])) { ?>
                    <span style="color: red;"><?php echo $errors['email']; ?></span>
                <?php } ?>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="********">
                <?php if (isset($errors['password'])) { ?>
                    <span style="color: red;"><?php echo $errors['password']; ?></span>
                <?php } ?>

                <label for="confirm-password">Password again</label>
                <input type="password" name="confirm-password" id="confirm-password" placeholder="********">
                <?php if (isset($errors['confirm-password'])) { ?>
                    <span style="color: red;"><?php echo $errors['confirm-password']; ?></span>
                <?php } ?>

                <button type="submit">Register</button>
            </form>
        </div>
    </main>
</body>

</html>