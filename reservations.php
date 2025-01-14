<?php
session_start();
require_once 'storage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];

function getUserReservations($userEmail)
{
    $reservationStorage = new Storage(new JsonIO('reservations.json'));
    return $reservationStorage->findAll(['user_email' => $userEmail]);
}

$userReservations = getUserReservations($user['email']);


if (isset($_GET['delete_reservation'])) {
    $reservationIdToDelete = $_GET['delete_reservation'];

    $reservationStorage = new Storage(new JsonIO('reservations.json'));

    $reservationStorage->delete($reservationIdToDelete);

    header('Location: reservations.php');
    exit();
}
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
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
           
            <a href="reservations.php" class="button">My Reservations</a>
            <a href="logout.php" class="button">Logout</a>
        </div>
    </header>

    <main>
        <section>
            <h2>My Reservations</h2>
            <!-- PRINTING USERNAME TO SHOW here -->
            <p style="margin:10px "><strong>Name:</strong> <?php echo ($user['fullname']); ?></p> <!-- Show the user's name -->
            <?php if (empty($userReservations)): ?>
                <p>No reservations found.</p>
            <?php else: ?>
                <div class="reservations-list">
                    <!-- priting user all reservations -->
                    <?php foreach ($userReservations as $reservation): ?>
                        <div class="reservation-card">
                            <img src="<?php echo ($reservation['car_image']); ?>" alt="<?php echo ($reservation['car_name']); ?>" />
                            <div class="reservation-details">
                                <p><strong>Car:</strong> <?php echo ($reservation['car_name']); ?></p>
                                <p><strong>From:</strong> <?php echo ($reservation['start_date']); ?></p>
                                <p><strong>To:</strong> <?php echo ($reservation['end_date']); ?></p>
                                <a href="reservations.php?delete_reservation=<?php echo ($reservation['id']); ?>" onclick="return confirm('Are you sure you want to delete this reservation?')" class="delete-button">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>