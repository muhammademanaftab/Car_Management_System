<?php
session_start();
require_once 'storage.php'; // Your Storage.php file

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user']);
$user = $is_logged_in ? $_SESSION['user'] : null;

// Initialize the JSON file handler for cars
$carStorage = new Storage(new JsonIO('cars.json'));

// Fetch all cars
$cars = $carStorage->findAll();

// Optional: Add filtering logic based on user inputs (seats, transmission, price, etc.)
$seats = isset($_GET['seats']) ? $_GET['seats'] : 0;
$transmission = isset($_GET['transmission']) ? $_GET['transmission'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 99999;

// Filter the cars based on user input
$filtered_cars = array_filter($cars, function ($car) use ($seats, $transmission, $min_price, $max_price) {
    return (
        ($seats == 0 || $car['passengers'] >= $seats) &&
        ($transmission == '' || $car['transmission'] == $transmission) &&
        ($car['daily_price_huf'] >= $min_price && $car['daily_price_huf'] <= $max_price)
    );
});

// Include the user's reservations if logged in
if ($is_logged_in) {
    $userReservations = getUserReservations($user['email']);
} else {
    $userReservations = [];
}

function getUserReservations($userEmail) {
    // Assuming user reservations are stored in 'reservations.json'
    $reservationStorage = new Storage(new JsonIO('reservations.json'));

    // Fetch all reservations for the logged-in user
    $userReservations = $reservationStorage->findAll(['user_email' => $userEmail]);

    return $userReservations;
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
                <!-- Profile dropdown -->
                <div class="profile-dropdown">
                    <button class="profile-btn">Welcome, <?php echo htmlspecialchars($user['name']); ?></button>
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
        <section class="hero">
            <h1>Rent cars easily!</h1>
        </section>

        <!-- Filter Form Section -->
        <section class="filter-section">
            <form class="filter-form" method="GET">
                <div>
                    <label for="seats">Seats</label>
                    <input type="number" name="seats" id="seats" value="<?php echo $_GET['seats'] ?? ''; ?>" min="0">
                </div>
                <div>
                    <label for="from">From</label>
                    <input type="date" name="from" id="from" value="<?php echo $_GET['from'] ?? ''; ?>">
                </div>
                <div>
                    <label for="until">Until</label>
                    <input type="date" name="until" id="until" value="<?php echo $_GET['until'] ?? ''; ?>">
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
                    <input type="number" name="min_price" id="min_price" value="<?php echo $_GET['min_price'] ?? 0; ?>">
                </div>
                <div>
                    <label for="max_price">Max Price</label>
                    <input type="number" name="max_price" id="max_price" value="<?php echo $_GET['max_price'] ?? 99999; ?>">
                </div>
                <button type="submit">Filter</button>
            </form>
        </section>

        <!-- Car Listings Section -->
        <section class="car-list">
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
        </section>
    </main>
</body>
</html>
