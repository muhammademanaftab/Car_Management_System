<?php
session_start();
require_once 'storage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Get the car ID from URL
$car_id = $_GET['car_id'] ?? null;
if (!$car_id) {
    header('Location: homepage.php');
    exit();
}

// Fetch the car details
$carStorage = new Storage(new JsonIO('cars.json'));
$car = $carStorage->findById($car_id);

// Check if the car was found
if (!$car) {
    echo "Car not found!";
    exit();
}

// Initialize errors array
$errors = [];

// Handle the booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? '';
    $until_date = $_POST['until_date'] ?? '';
    $user_email = $_SESSION['user']['email'];
    $today = date('Y-m-d');

    // Validate the dates
    if (!$from_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date) || $from_date < $today) {
        $errors['from_date'] = "The 'From' date must be today or later and in the correct format (YYYY-MM-DD).";
    }
    if (!$until_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $until_date) || $until_date <= $from_date) {
        $errors['until_date'] = "The 'Until' date must be after the 'From' date and in the correct format (YYYY-MM-DD).";
    }

    // If no errors, proceed with booking
    if (empty($errors)) {
        $reservationStorage = new Storage(new JsonIO('reservations.json'));
        $reservation = [
            'car_id' => $car['id'],
            'car_name' => $car['brand'] . ' ' . $car['model'],
            'car_image' => $car['image'],
            'start_date' => $from_date,
            'end_date' => $until_date,
            'user_email' => $user_email
        ];
        $reservationStorage->add($reservation);

        header('Location: profile.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Car - iKarRental</title>
    <link rel="stylesheet" href="book.css">
</head>
<body>
    <header>
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="profile-dropdown">
                    <button class="profile-btn">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></button>
                    <div class="dropdown-content">
                        <a href="profile.php">Profile Settings</a>
                        <a href="reservations.php">My Reservations</a>
                    </div>
                </div>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="registration.php" class="button">Registration</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="book-section">
            <h2>Book <?php echo htmlspecialchars($car['brand']) . ' ' . htmlspecialchars($car['model']); ?></h2>

            <div class="car-details">
                <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="Car Image" class="car-image">
                <p><strong>Brand:</strong> <?php echo htmlspecialchars($car['brand']); ?></p>
                <p><strong>Model:</strong> <?php echo htmlspecialchars($car['model']); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($car['year']); ?></p>
                <p><strong>Fuel Type:</strong> <?php echo htmlspecialchars($car['fuel_type']); ?></p>
                <p><strong>Transmission:</strong> <?php echo htmlspecialchars($car['transmission']); ?></p>
                <p><strong>Seats:</strong> <?php echo htmlspecialchars($car['passengers']); ?></p>
                <p><strong>Price per Day:</strong> <?php echo number_format($car['daily_price_huf']); ?> Ft/day</p>
            </div>

            <form method="POST">
                <div>
                    <label for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" value="<?php echo htmlspecialchars($_POST['from_date'] ?? ''); ?>" required>
                    <?php if (isset($errors['from_date'])): ?>
                        <p class="error" style="margin-top: 10px;"><?php echo $errors['from_date']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="until_date">Until</label>
                    <input type="date" name="until_date" id="until_date" value="<?php echo htmlspecialchars($_POST['until_date'] ?? ''); ?>" required>
                    <?php if (isset($errors['until_date'])): ?>
                        <p class="error" style="margin-top: 10px;"><?php echo $errors['until_date']; ?></p>
                    <?php endif; ?>
                </div>
                        
                <button type="submit">Confirm Booking</button>
            </form>
        </section>
    </main>
</body>
</html>
