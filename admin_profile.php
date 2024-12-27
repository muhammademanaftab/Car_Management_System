<?php
session_start();
require_once 'storage.php';

// Check if the user is an admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Initialize storages
$carStorage = new Storage(new JsonIO('cars.json'));
$reservationStorage = new Storage(new JsonIO('reservations.json'));

// Fetch all cars and reservations
$cars = $carStorage->findAll();
$reservations = $reservationStorage->findAll();

$errors = []; // To store validation errors

// Handle car deletion
if (isset($_GET['delete_car'])) {
    $carIdToDelete = $_GET['delete_car'];

    // Delete all related reservations
    $reservationStorage->deleteMany(function ($reservation) use ($carIdToDelete) {
        return $reservation['car_id'] === $carIdToDelete;
    });

    // Delete the car itself
    $carStorage->delete($carIdToDelete);

    header('Location: admin_profile.php');
    exit();
}

// Handle reservation deletion
if (isset($_GET['delete_reservation'])) {
    $reservationIdToDelete = $_GET['delete_reservation'];

    // Delete the reservation
    $reservationStorage->delete($reservationIdToDelete);

    header('Location: admin_profile.php');
    exit();
}

// Handle reservation editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_reservation'])) {
    $reservationId = $_POST['reservation_id'];
    $editedReservation = [
        'car_name' => $_POST['car_name'],
        'user_email' => $_POST['user_email'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'car_id' => $_POST['car_id'], // Ensure car_id is retained
        'image' => $_POST['image'] ?? '' // Handle missing image gracefully
    ];

    $reservationStorage->update($reservationId, $editedReservation);

    header('Location: admin_profile.php');
    exit();
}

// Fetch reservation data for editing
if (isset($_GET['edit_reservation'])) {
    $reservationIdToEdit = $_GET['edit_reservation'];
    $reservationToEdit = $reservationStorage->findById($reservationIdToEdit);
} else {
    $reservationToEdit = null;
}

// Handle adding or editing cars
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_car']) || isset($_POST['edit_car'])) {
        $brand = trim($_POST['brand']);
        $model = trim($_POST['model']);
        $year = (int)$_POST['year'];
        $fuelType = $_POST['fuel_type'];
        $transmission = $_POST['transmission'];
        $passengers = (int)$_POST['passengers'];
        $price = (int)$_POST['price'];
        $image = trim($_POST['image']);

        // Validate inputs
        if (empty($brand)) $errors['brand'] = "Brand is required.";
        if (empty($model)) $errors['model'] = "Model is required.";
        if ($year <= 1900 || $year > date('Y')) $errors['year'] = "Invalid year.";
        if (empty($fuelType)) $errors['fuel_type'] = "Fuel type is required.";
        if (empty($transmission)) $errors['transmission'] = "Transmission is required.";
        if ($passengers <= 0) $errors['passengers'] = "Seats must be valid.";
        if ($price <= 0) $errors['price'] = "Price must be greater than zero.";
        if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
            $errors['image'] = "Invalid image URL.";
        }

        if (isset($_POST['add_car']) && empty($errors)) {
            // Add a new car
            $newCar = [
                'brand' => $brand,
                'model' => $model,
                'year' => $year,
                'fuel_type' => $fuelType,
                'transmission' => $transmission,
                'passengers' => $passengers,
                'daily_price_huf' => $price,
                'image' => $image,
            ];
            $carStorage->add($newCar);
            header('Location: admin_profile.php');
            exit();
        }

        if (isset($_POST['edit_car']) && empty($errors)) {
            // Edit an existing car
            $carIdToEdit = $_POST['car_id'];
            $editedCar = [
                'brand' => $brand,
                'model' => $model,
                'year' => $year,
                'fuel_type' => $fuelType,
                'transmission' => $transmission,
                'passengers' => $passengers,
                'daily_price_huf' => $price,
                'image' => $image,
            ];
            $carStorage->update($carIdToEdit, $editedCar);
            header('Location: admin_profile.php');
            exit();
        }
    }
}

// Fetch car data for editing
if (isset($_GET['edit_car'])) {
    $carIdToEdit = $_GET['edit_car'];
    $carToEdit = $carStorage->findById($carIdToEdit);
} else {
    $carToEdit = null;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin_profile.css">
    <title>Admin Profile - iKarRental</title>
</head>

<body>
    <header>
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
            <a href="admin_profile.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <main>
        <h1>Welcome Admin</h1>

        <h2>All Car Reservations</h2>
        <table>
            <tr>
                <th>Car Name</th>
                <th>User</th>
                <th>From</th>
                <th>Until</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($reservations as $id => $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['car_name']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['user_email']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['end_date']); ?></td>
                    <td>
                        <a href="admin_profile.php?edit_reservation=<?php echo urlencode($id); ?>">Edit</a> |
                        <a href="admin_profile.php?delete_reservation=<?php echo urlencode($id); ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if ($reservationToEdit): ?>
            <h3>Edit Reservation</h3>
            <form method="POST">
                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservationIdToEdit); ?>">

                <label for="car_name">Car Name</label>
                <input type="text" name="car_name" id="car_name" value="<?php echo htmlspecialchars($reservationToEdit['car_name']); ?>" required>

                <label for="user_email">User Email</label>
                <input type="email" name="user_email" id="user_email" value="<?php echo htmlspecialchars($reservationToEdit['user_email']); ?>" required>

                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($reservationToEdit['start_date']); ?>" required>

                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($reservationToEdit['end_date']); ?>" required>

                <label for="image">Image URL</label>
                <input type="text" name="image" id="image" value="<?php echo htmlspecialchars($reservationToEdit['image'] ?? ''); ?>" required>

                <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($reservationToEdit['car_id']); ?>">

                <button type="submit" name="edit_reservation">Save Changes</button>
            </form>
        <?php endif; ?>

        <h2>Manage Cars</h2>

        <h3>Add New Car</h3>
        <form method="POST">
            <label for="brand">Brand</label>
            <input type="text" name="brand" id="brand" required>

            <label for="model">Model</label>
            <input type="text" name="model" id="model" required>

            <label for="year">Year</label>
            <input type="number" name="year" id="year" required>

            <label for="fuel_type">Fuel Type</label>
            <select name="fuel_type" id="fuel_type" required>
                <option value="Petrol">Petrol</option>
                <option value="Diesel">Diesel</option>
                <option value="Electric">Electric</option>
            </select>

            <label for="transmission">Transmission</label>
            <select name="transmission" id="transmission" required>
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
            </select>

            <label for="passengers">Seats</label>
            <input type="number" name="passengers" id="passengers" required>

            <label for="price">Price per Day (HUF)</label>
            <input type="number" name="price" id="price" required>

            <label for="image">Image URL</label>
            <input type="text" name="image" id="image" required>

            <button type="submit" name="add_car">Add Car</button>
        </form>

        <?php if ($carToEdit): ?>
            <h3>Edit Car</h3>
            <form method="POST">
                <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($carToEdit['id']); ?>">

                <label for="brand">Brand</label>
                <input type="text" name="brand" id="brand" value="<?php echo htmlspecialchars($carToEdit['brand']); ?>" required>

                <label for="model">Model</label>
                <input type="text" name="model" id="model" value="<?php echo htmlspecialchars($carToEdit['model']); ?>" required>

                <label for="year">Year</label>
                <input type="number" name="year" id="year" value="<?php echo htmlspecialchars($carToEdit['year']); ?>" required>

                <label for="fuel_type">Fuel Type</label>
                <select name="fuel_type" id="fuel_type" required>
                    <option value="Petrol" <?php echo $carToEdit['fuel_type'] === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                    <option value="Diesel" <?php echo $carToEdit['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                    <option value="Electric" <?php echo $carToEdit['fuel_type'] === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                </select>

                <label for="transmission">Transmission</label>
                <select name="transmission" id="transmission" required>
                    <option value="Automatic" <?php echo $carToEdit['transmission'] === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                    <option value="Manual" <?php echo $carToEdit['transmission'] === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                </select>

                <label for="passengers">Seats</label>
                <input type="number" name="passengers" id="passengers" value="<?php echo htmlspecialchars($carToEdit['passengers']); ?>" required>

                <label for="price">Price per Day (HUF)</label>
                <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($carToEdit['daily_price_huf']); ?>" required>

                <label for="image">Image URL</label>
                <input type="text" name="image" id="image" value="<?php echo htmlspecialchars($carToEdit['image']); ?>" required>

                <button type="submit" name="edit_car">Save Changes</button>
            </form>
        <?php endif; ?>

        <h3>Existing Cars</h3>
        <ul>
            <?php foreach ($cars as $id => $car): ?>
                <li>
                    <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                    <a href="admin_profile.php?edit_car=<?php echo urlencode($id); ?>">Edit</a> |
                    <a href="admin_profile.php?delete_car=<?php echo urlencode($id); ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>

</html>
