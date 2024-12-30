<?php
session_start();
require_once 'storage.php';

// Check if the user is logged in
$is_logged_in = false;
if (isset($_SESSION['user'])) {
    $is_logged_in = true;
    $user = $_SESSION['user'];
} else {
    $user = null;
}

// Initialize the JSON file handlers
$carStorage = new Storage(new JsonIO('cars.json'));
$reservationStorage = new Storage(new JsonIO('reservations.json'));

// Fetch all cars and reservations
$cars = $carStorage->findAll();
$reservations = $reservationStorage->findAll();

// Get today's date
$today = date('Y-m-d');

// Initialize variables for filtering dates
$from_date = '';
$until_date = '';

if (isset($_GET['from'])) {
    $from_date = $_GET['from'];
}

if (isset($_GET['until'])) {
    $until_date = $_GET['until'];
}

// Filter out booked cars
$available_cars = [];
foreach ($cars as $car) {
    $is_available = true;

    foreach ($reservations as $reservation) {
        if ($reservation['car_id'] == $car['id']) {
            // Check if the car is booked for the current or user-specified period
            $reservation_start = $reservation['start_date'];
            $reservation_end = $reservation['end_date'];

            if (($from_date != '' && $until_date != '' &&
                (($reservation_start <= $until_date && $reservation_end >= $from_date) ||
                ($reservation_start <= $today && $reservation_end >= $today))) ||
                ($from_date == '' && $until_date == '' && $reservation_start <= $today && $reservation_end >= $today)) {
                $is_available = false;
                break;
            }
        }
    }

    if ($is_available) {
        $available_cars[] = $car;
    }
}

// Initialize variables for validation and errors
$errors = [];
$seats = 0;
if (isset($_GET['seats'])) {
    $seats = (int)$_GET['seats'];
}

$transmission = '';
if (isset($_GET['transmission'])) {
    $transmission = trim($_GET['transmission']);
}

$min_price = 0;
if (isset($_GET['min_price'])) {
    $min_price = (int)$_GET['min_price'];
}

$max_price = 99999;
if (isset($_GET['max_price'])) {
    $max_price = (int)$_GET['max_price'];
}

$from_date = '';
if (isset($_GET['from'])) {
    $from_date = $_GET['from'];
}

$until_date = '';
if (isset($_GET['until'])) {
    $until_date = $_GET['until'];
}

// Validate dates
if ($from_date != '') {
    if (!strtotime($from_date) || $from_date < $today) {
        $errors['from'] = "The 'From' date must be today or later and in a valid format.";
    }
}

if ($until_date != '') {
    if (!strtotime($until_date) || $until_date <= $from_date) {
        $errors['until'] = "The 'Until' date must be later than the 'From' date and in a valid format.";
    }
}

// Validate prices
if ($min_price < 0) {
    $errors['min_price'] = "The minimum price must be 0 or greater.";
}
if ($max_price < $min_price) {
    $errors['max_price'] = "The maximum price must be greater than or equal to the minimum price.";
}

// Filter cars based on user input
$filtered_cars = [];
if (empty($errors)) {
    foreach ($available_cars as $car) {
        $matches = true;

        // Filter by seats
        if ($seats > 0 && $car['passengers'] < $seats) {
            $matches = false;
        }

        // Filter by transmission
        if ($transmission != '' && strtolower($car['transmission']) != strtolower($transmission)) {
            $matches = false;
        }

        // Filter by price
        if ($car['daily_price_huf'] < $min_price || $car['daily_price_huf'] > $max_price) {
            $matches = false;
        }

        // Filter by availability dates
        if ($from_date != '' && isset($car['available_from']) && $car['available_from'] > $from_date) {
            $matches = false;
        }
        if ($until_date != '' && isset($car['available_until']) && $car['available_until'] < $until_date) {
            $matches = false;
        }

        if ($matches) {
            $filtered_cars[] = $car;
        }
    }
}

// Include the user's reservations if logged in
$userReservations = [];
if ($is_logged_in) {
    $userReservations = getUserReservations($user['email']);
}

function getUserReservations($userEmail) {
    $reservationStorage = new Storage(new JsonIO('reservations.json'));
    return $reservationStorage->findAll(['user_email' => $userEmail]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental</title>
    <link rel="stylesheet" href="homepage.css">
</head>

<body>
<header>
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
            <?php if ($is_logged_in): ?>
                <div class="profile-dropdown">
                    <button class="profile-btn">Welcome, <?php echo htmlspecialchars($user['fullname']); ?></button>
                    <div class="dropdown-content">
                        <a href="reservations.php">My Reservations</a>
                    </div>
                </div>
                <a href="logout.php" class="button">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="registration.php" class="button">Registration</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>Rent cars easily!</h1>
        </section>

        <section class="filter-section">
            <form class="filter-form" method="GET">
                <div>
                    <label for="seats">Seats</label>
                    <input type="number" name="seats" id="seats" value="<?php echo htmlspecialchars($_GET['seats'] ?? ''); ?>" min="0">
                </div>
                <div>
                    <label for="from">From</label>
                    <input type="date" name="from" id="from" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                    <?php if (isset($errors['from'])): ?>
                        <p class="error"><?php echo $errors['from']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="until">Until</label>
                    <input type="date" name="until" id="until" value="<?php echo htmlspecialchars($_GET['until'] ?? ''); ?>">
                    <?php if (isset($errors['until'])): ?>
                        <p class="error"><?php echo $errors['until']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="transmission">Gear type</label>
                    <select name="transmission" id="transmission">
                        <option value="">Any</option>
                        <option value="automatic" <?php echo ($_GET['transmission'] ?? '') === 'automatic' ? 'selected' : ''; ?>>Automatic</option>
                        <option value="manual" <?php echo ($_GET['transmission'] ?? '') === 'manual' ? 'selected' : ''; ?>>Manual</option>
                    </select>
                </div>
                <div>
                    <label for="min_price">Min Price</label>
                    <input type="number" name="min_price" id="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? 0); ?>">
                    <?php if (isset($errors['min_price'])): ?>
                        <p class="error"><?php echo $errors['min_price']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="max_price">Max Price</label>
                    <input type="number" name="max_price" id="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? 99999); ?>">
                    <?php if (isset($errors['max_price'])): ?>
                        <p class="error"><?php echo $errors['max_price']; ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit">Filter</button>
            </form>
        </section>

        <section class="car-list">
            <?php if (!empty($errors)): ?>
                <p>Please correct the errors above.</p>
            <?php elseif (empty($filtered_cars)): ?>
                <p>No cars found matching your filters.</p>
            <?php else: ?>
                <?php foreach ($filtered_cars as $car): ?>
                    <div class="car-card">
                        <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['brand']); ?> <?php echo htmlspecialchars($car['model']); ?>" class="car-image">
                        <div class="car-info">
                            <h3 class="car-name"><?php echo htmlspecialchars($car['brand']) . " " . htmlspecialchars($car['model']); ?></h3>
                            <p class="car-price"><?php echo number_format($car['daily_price_huf']); ?> Ft/day</p>
                            <p class="car-details"><?php echo htmlspecialchars($car['fuel_type']); ?> | <?php echo htmlspecialchars($car['transmission']); ?> | Year: <?php echo htmlspecialchars($car['year']); ?> | Passengers: <?php echo htmlspecialchars($car['passengers']); ?></p>
                            <?php if ($is_logged_in): ?>
                                <button class="book-button" onclick="window.location.href='book.php?car_id=<?php echo $car['id']; ?>'">Book</button>
                            <?php else: ?>
                                <button class="book-button" onclick="alert('Please log in to book.')">Book</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
