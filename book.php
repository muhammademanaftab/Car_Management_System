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

    // Fetch existing reservations for the car
    $reservationStorage = new Storage(new JsonIO('reservations.json'));
    $existing_reservations = $reservationStorage->findAll(['car_id' => $car_id]);

    // Validate the dates
    if (!$from_date || !strtotime($from_date) || $from_date < $today) {
        $errors['from_date'] = "The 'From' date must be today or later and in the correct format (YYYY-MM-DD).";
    }
    if (!$until_date || !strtotime($until_date) || $until_date <= $from_date) {
        $errors['until_date'] = "The 'Until' date must be after the 'From' date and in the correct format (YYYY-MM-DD).";
    }

    // Check for overlapping reservations
    foreach ($existing_reservations as $reservation) {
        $reservation_start = $reservation['start_date'];
        $reservation_end = $reservation['end_date'];

        // Overlap conditions
        if (
            ($from_date >= $reservation_start && $from_date <= $reservation_end) || // Start date is within an existing reservation
            ($until_date >= $reservation_start && $until_date <= $reservation_end) || // End date is within an existing reservation
            ($from_date <= $reservation_start && $until_date >= $reservation_end)    // Fully overlaps an existing reservation
        ) {
            $errors['reservation'] = "The car is already booked for the selected dates.";
            break;
        }
    }

    if (empty($errors)) {
        $reservation = [
            'car_id' => $car['id'],
            'car_name' => $car['brand'] . ' ' . $car['model'],
            'car_image' => $car['image'],
            'start_date' => $from_date,
            'end_date' => $until_date,
            'user_email' => $user_email
        ];
        $reservationStorage->add($reservation);

        // Set a session variable to pass success message and booking details
        $_SESSION['booking_success'] = [
            'message' => "Your booking for " . htmlspecialchars($car['brand'] . ' ' . $car['model']) . " has been successfully confirmed!",
            'details' => $reservation
        ];
        header('Location: book.php?car_id=' . $car_id);
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
                <!-- <div class="profile-dropdown">
                    <button class="profile-btn">Welcome, <?php echo htmlspecialchars($_SESSION['user']['fullname']); ?></button>
                    <div class="dropdown-content">
                        <a href="profile.php">Profile Settings</a>
                        <a href="reservations.php">My Reservations</a>
                    </div>
                </div> -->

                <a href="reservations.php" class="button">My Reservations</a>
                <a href="logout.php" class="button">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="registration.php" class="button">Registration</a>
            <?php endif; ?>
        </div>
    </header>


    <?php if (isset($_SESSION['booking_success'])): ?>
        <div class="success-overlay">
            <div class="success-message">
                <p><?php echo $_SESSION['booking_success']['message']; ?></p>
                <h3>Booking Details</h3>
                <ul>
                    <li><strong>Car:</strong> <?php echo htmlspecialchars($_SESSION['booking_success']['details']['car_name']); ?></li>
                    <li><strong>From:</strong> <?php echo htmlspecialchars($_SESSION['booking_success']['details']['start_date']); ?></li>
                    <li><strong>Until:</strong> <?php echo htmlspecialchars($_SESSION['booking_success']['details']['end_date']); ?></li>
                </ul>
                <h3>Car Attributes</h3>
                <ul>
                    <li><strong>Brand:</strong> <?php echo htmlspecialchars($car['brand']); ?></li>
                    <li><strong>Model:</strong> <?php echo htmlspecialchars($car['model']); ?></li>
                    <li><strong>Year:</strong> <?php echo htmlspecialchars($car['year']); ?></li>
                    <li><strong>Fuel Type:</strong> <?php echo htmlspecialchars($car['fuel_type']); ?></li>
                    <li><strong>Transmission:</strong> <?php echo htmlspecialchars($car['transmission']); ?></li>
                    <li><strong>Seats:</strong> <?php echo htmlspecialchars($car['passengers']); ?></li>
                    <li><strong>Price per Day:</strong> <?php echo number_format($car['daily_price_huf']); ?> Ft/day</li>
                </ul>
            </div>
        </div>
        <?php unset($_SESSION['booking_success']); // Clear the session variable 
        ?>
    <?php endif; ?>

    <main>
        <section class="book-section">
            <h2>Book <?php echo htmlspecialchars($car['brand']) . ' ' . htmlspecialchars($car['model']); ?></h2>

            <?php if (isset($_SESSION['booking_success'])): ?>
                <div class="success-message">
                    <p><?php echo $_SESSION['booking_success']['message']; ?></p>
                    <h3>Booking Details</h3>
                    <ul>
                        <li><strong>Car:</strong> <?php echo htmlspecialchars($_SESSION['booking_success']['details']['car_name']); ?></li>
                        <li><strong>From:</strong> <?php echo htmlspecialchars($_SESSION['booking_success']['details']['start_date']); ?></li>
                        <li><strong>Until:</strong> <?php echo htmlspecialchars($_SESSION['booking_success']['details']['end_date']); ?></li>
                    </ul>
                    <h3>Car Attributes</h3>
                    <ul>
                        <li><strong>Brand:</strong> <?php echo htmlspecialchars($car['brand']); ?></li>
                        <li><strong>Model:</strong> <?php echo htmlspecialchars($car['model']); ?></li>
                        <li><strong>Year:</strong> <?php echo htmlspecialchars($car['year']); ?></li>
                        <li><strong>Fuel Type:</strong> <?php echo htmlspecialchars($car['fuel_type']); ?></li>
                        <li><strong>Transmission:</strong> <?php echo htmlspecialchars($car['transmission']); ?></li>
                        <li><strong>Seats:</strong> <?php echo htmlspecialchars($car['passengers']); ?></li>
                        <li><strong>Price per Day:</strong> <?php echo number_format($car['daily_price_huf']); ?> Ft/day</li>
                    </ul>
                </div>
                <?php unset($_SESSION['booking_success']); // Clear the session variable 
                ?>
            <?php endif; ?>

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
                        <p class="error" style="margin-top: 10px; color: red;"><?php echo $errors['from_date']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="until_date">Until</label>
                    <input type="date" name="until_date" id="until_date" value="<?php echo htmlspecialchars($_POST['until_date'] ?? ''); ?>" required>
                    <?php if (isset($errors['until_date'])): ?>
                        <p class="error" style="margin-top: 10px; color: red;"><?php echo $errors['until_date']; ?></p>
                    <?php endif; ?>
                </div>

                <?php if (isset($errors['reservation'])): ?>
                    <p class="error" style="margin-top: 10px; color: red;"><?php echo $errors['reservation']; ?></p>
                <?php endif; ?>

                <button type="submit">Confirm Booking</button>
            </form>
        </section>

    </main>
</body>

</html>