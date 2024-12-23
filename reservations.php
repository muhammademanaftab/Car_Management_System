<?php
session_start();
require_once 'storage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];

function getUserReservations($userEmail) {
    $reservationStorage = new Storage(new JsonIO('reservations.json'));
    return $reservationStorage->findAll(['user_email' => $userEmail]);
}

$userReservations = getUserReservations($user['email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - iKarRental</title>
    <link rel="stylesheet" href="reservations.css">
</head>
<body>
    <header>
        <div class="logo">iKarRental</div>
        <div class="nav">
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <main>
        <h2>My Reservations</h2>
        <?php if (empty($userReservations)): ?>
            <p>No reservations found.</p>
        <?php else: ?>
            <div class="reservations-list">
                <?php foreach ($userReservations as $reservation): ?>
                    <div class="reservation-card">
                        <img src="<?php echo $reservation['car_image']; ?>" alt="<?php echo $reservation['car_name']; ?>" />
                        <p>Car: <?php echo $reservation['car_name']; ?></p>
                        <p>From: <?php echo $reservation['start_date']; ?> To: <?php echo $reservation['end_date']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
