<?php
session_start();
require_once 'storage.php'; 

$userStorage = new Storage(new JsonIO('users.json'));

$email = $password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $user = $userStorage->findOne(['email' => $email]);


        if ($user && $password === $user['password']) { 
            if ($user['status'] === 'admin') {
                $_SESSION['admin'] = true;
                header('Location: admin_profile.php');  
                exit();
            } else {
                $_SESSION['user'] = [
                    'fullname' => $user['fullname'],
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'id' => $user['id'],
                ];
                header('Location: homepage.php');  
                exit;
            }
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
        <div class="error"><?php echo ($errors['general']); ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <label for="email">Email address</label>
        <input type="email" name="email" id="email" placeholder="Email" value="<?php echo ($email); ?>">
        <?php if (isset($errors['email'])): ?>
          <div class="error"><?php echo ($errors['email']); ?></div>
        <?php endif; ?>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password">
        <?php if (isset($errors['password'])): ?>
          <div class="error"><?php echo ($errors['password']); ?></div>
        <?php endif; ?>

        <!-- submit btn bnaaa rha hin jo debug krne main elp krega -->
        <button type="submit">Login</button>
      </form>
    </div>
  </main>
</body>
</html>
