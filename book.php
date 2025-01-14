<?php
session_start();
require_once 'storage.php';

// makign login page and setting user...

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$car_id = $_GET['car_id'] ?? null;
if (!$car_id) {
    header('Location: homepage.php');
    exit();
}

$carStorage = new Storage(new JsonIO('cars.json'));
$car = $carStorage->findById($car_id);

if (!$car) {
    echo "Car not found!...";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Car - iKarRental</title>
    <link rel="stylesheet" href="book.css">
    <!-- inclusidng libraries for usage and script for flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="booking-handler.js" defer></script>
</head>

<body>
    <header>
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="reservations.php" class="button">My Reservations</a>
                <a href="logout.php" class="button">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="registration.php" class="button">Registration</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="book-section">
            <h2>Book <?php echo ($car['brand']) . ' ' . ($car['model']); ?></h2>

            <div class="car-details">
                <img src="<?php echo ($car['image']); ?>" alt="Car Image..." class="car-image">
                <p><strong>Brand:</strong> <?php echo ($car['brand']); ?></p>

                <p><strong>Model:</strong> <?php  echo ($car['model']); ?></p>

                <p><strong>Year:</strong> <?php echo ($car['year']); ?></p>

                <p><strong>Fuel Type:</strong> <?php echo ($car['fuel_type']); ?></p>

                <p><strong>Transmission:</strong> <?php echo ($car['transmission']); ?></p>

                <p><strong>Seats:</strong> <?php echo ($car['passengers']); ?></p>
                <p><strong>Price per Day:</strong> <?php echo number_format($car['daily_price_huf']); ?> Ft/day</p>
            </div>

            <form method="POST" id="bookingForm">

                <input type="hidden" name="car_id" value="<?php echo ($car_id); ?>">

                <div>
                    <label for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" required>
                </div>

                <div>
                    <label for="until_date">Until</label>

                    <input type="date" name="until_date" id="until_date" required>
                </div>

                <button type="submit">Confirm Booking</button>
            </form>
        </section>
    </main>
</body>

</html>