<?php
session_start();
require_once 'storage.php';

$is_logged_in = false;
if (isset($_SESSION['user'])) {
    $is_logged_in = true;
    $user = $_SESSION['user'];
} else {
    $user = null;
}

$carStorage = new Storage(new JsonIO('cars.json'));
$reservationStorage = new Storage(new JsonIO('reservations.json'));

$cars = $carStorage->findAll();
$reservations = $reservationStorage->findAll();

$today = date('Y-m-d');

$from_date = '';
$until_date = '';

if (isset($_GET['from'])) {
    $from_date = $_GET['from'];
}

if (isset($_GET['until'])) {
    $until_date = $_GET['until'];
}

$available_cars = [];

if ($from_date == '' && $until_date == '') {
    $available_cars = $cars;
} else {
    foreach ($cars as $car) {
        $is_available = true;

        foreach ($reservations as $reservation) {
            if ($reservation['car_id'] == $car['id']) {
                $reservation_start = $reservation['start_date'];
                $reservation_end = $reservation['end_date'];

                if (($from_date != '' && $until_date != '') &&
                    ($reservation_start <= $until_date && $reservation_end >= $from_date)) {
                    $is_available = false;
                    break;
                }
            }
        }

        if ($is_available) {
            $available_cars[] = $car;
        }
    }
}


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

if ($min_price < 0) {
    $errors['min_price'] = "The minimum price must be 0 or greater.";
}
if ($max_price < $min_price) {
    $errors['max_price'] = "The maximum price must be greater than or equal to the minimum price.";
}

$filtered_cars = [];
if (empty($errors)) {
    foreach ($available_cars as $car) {
        $matches = true;

        if ($seats > 0 && $car['passengers'] < $seats) {
            $matches = false;
        }

        if ($transmission != '' && strtolower($car['transmission']) != strtolower($transmission)) {
            $matches = false;
        }

        if ($car['daily_price_huf'] < $min_price || $car['daily_price_huf'] > $max_price) {
            $matches = false;
        }

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
                <a href="reservations.php" class="button">My Reservations</a>
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
                    <input type="number" name="seats" id="seats" value="<?php echo ($_GET['seats'] ?? ''); ?>" min="0">
                </div>
                <div>
                    <label for="from">From</label>
                    <input type="date" name="from" id="from" value="<?php echo ($_GET['from'] ?? ''); ?>">
                    <?php if (isset($errors['from'])): ?>
                        <p class="error"><?php echo $errors['from']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="until">Until</label>
                    <input type="date" name="until" id="until" value="<?php echo ($_GET['until'] ?? ''); ?>">
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
                    <input type="number" name="min_price" id="min_price" value="<?php echo ($_GET['min_price'] ?? 0); ?>">
                    <?php if (isset($errors['min_price'])): ?>
                        <p class="error"><?php echo $errors['min_price']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="max_price">Max Price</label>
                    <input type="number" name="max_price" id="max_price" value="<?php echo ($_GET['max_price'] ?? 99999); ?>">
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
            <a href="book.php?car_id=<?php echo $car['id']; ?>" class="car-link">
                <div class="car-card">
                    <img src="<?php echo ($car['image']); ?>" alt="<?php echo ($car['brand']); ?> <?php echo ($car['model']); ?>" class="car-image">
                    <div class="car-info">
                        <h3 class="car-name"><?php echo ($car['brand']) . " " . ($car['model']); ?></h3>
                        <p class="car-price"><?php echo number_format($car['daily_price_huf']); ?> Ft/day</p>
                        <p class="car-details"><?php echo ($car['fuel_type']); ?> | <?php echo ($car['transmission']); ?> | Year: <?php echo ($car['year']); ?> | Passengers: <?php echo ($car['passengers']); ?></p>
                        <button class="book-button">Book</button>
                    </div>
                    
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

    </main>
</body>
</html>
