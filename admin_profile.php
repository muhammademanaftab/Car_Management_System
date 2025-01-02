<?php
require_once 'storage.php';

$carsStorage = new Storage(new JsonIO('cars.json'));
$reservationsStorage = new Storage(new JsonIO('reservations.json'));

$errors = [];
$editCarId = null; // Track which car is being edited
$editingCar = null; // Store the car being edited
$showAddForm = false; // Toggle the Add New Car form

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
           
            case 'add_car':
                $brand = $_POST['brand'] ?? '';
                $model = $_POST['model'] ?? '';
                $year = $_POST['year'] ?? '';
                $transmission = $_POST['transmission'] ?? '';
                $fuel_type = $_POST['fuel_type'] ?? '';
                $passengers = $_POST['passengers'] ?? '';
                $daily_price_huf = $_POST['daily_price_huf'] ?? '';
                $image = $_POST['image'] ?? '';
            
                // Validate fields
                if (empty($brand) || !preg_match("/^[a-zA-Z\s]+$/", $brand)) {
                    $errors['brand'] = "Brand must be a valid text and cannot contain numbers or special characters.";
                }
                if (empty($model) || !preg_match("/^[a-zA-Z\s]+$/", $model)) {
                    $errors['model'] = "Model must be a valid text and cannot contain numbers or special characters.";
                }
                if (empty($year) || !is_numeric($year) || $year < 1900 || $year > date('Y')) {
                    $errors['year'] = "Year must be a valid number between 1900 and the current year.";
                }
                if (!in_array($transmission, ['Manual', 'Automatic'])) {
                    $errors['transmission'] = "Transmission must be 'Manual' or 'Automatic'.";
                }
                if (!in_array($fuel_type, ['Petrol', 'Electric', 'Diesel'])) {
                    $errors['fuel_type'] = "Fuel type must be 'Petrol', 'Electric', or 'Diesel'.";
                }
                if (empty($passengers) || !is_numeric($passengers) || $passengers <= 0) {
                    $errors['passengers'] = "Passengers must be a positive number.";
                }
                if (empty($daily_price_huf) || !is_numeric($daily_price_huf) || $daily_price_huf <= 0) {
                    $errors['daily_price_huf'] = "Daily price must be a positive number.";
                }
                if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
                    $errors['image'] = "Image must be a valid URL.";
                }
            
                // If no errors, add the car
                if (empty($errors)) {
                    $newCar = [
                        'brand' => $brand,
                        'model' => $model,
                        'year' => (int)$year,
                        'transmission' => $transmission,
                        'fuel_type' => $fuel_type,
                        'passengers' => (int)$passengers,
                        'daily_price_huf' => (int)$daily_price_huf,
                        'image' => $image,
                    ];
                    $carsStorage->add($newCar);
                }
                $showAddForm = true;
                break;
            

            case 'edit_car':
                $editCarId = $_POST['id']; // Set the car to be edited
                $editingCar = $carsStorage->findById($editCarId);
                break;

            case 'save_car':
                $carId = $_POST['id'];
                $updatedCar = [
                    'id' => (int)$carId,
                    'brand' => $_POST['brand'],
                    'model' => $_POST['model'],
                    'year' => (int)$_POST['year'],
                    'transmission' => $_POST['transmission'],
                    'fuel_type' => $_POST['fuel_type'],
                    'passengers' => (int)$_POST['passengers'],
                    'daily_price_huf' => (int)$_POST['daily_price_huf'],
                    'image' => $_POST['image'],
                ];
                $carsStorage->update($carId, $updatedCar);
                $editCarId = null; // Clear editing state
                $editingCar = null;
                break;

            case 'cancel_edit':
                $editCarId = null; // Clear editing state
                $editingCar = null;
                break;

            case 'delete_car':
                $carId = $_POST['id'];
                // Delete related reservations
                $reservationsStorage->deleteMany(fn($reservation) => $reservation['car_id'] == $carId);
                // Delete the car
                $carsStorage->delete($carId);
                break;

            case 'toggle_add_form':
                $showAddForm = !$showAddForm;
                break;

            case 'delete_reservation':
                $reservationId = $_POST['id'];
                $reservationsStorage->delete($reservationId);
                break;
        }
    }
}

// Fetch all cars and reservations
$cars = $carsStorage->findAll();
$reservations = $reservationsStorage->findAll();
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="admin_profile.css">
    <title>Admin Profile</title>
    <script>
        function toggleForm(formId) {
            document.getElementById(formId).classList.toggle('visible');
        }
    </script>
</head>

<body>

    <header>
        <div class="logo"><a href="homepage.php">iKarRental</a></div>
        <div class="nav">
            <a href="admin_profile.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <div class="container">
        <h1>Admin Profile</h1>

        <h2>All Reservations</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Car ID</th>
                <th>Car Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>User Email</th>
                <th>Action</th>
            </tr>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= $reservation['id'] ?></td>
                    <td><?= $reservation['car_id'] ?></td>
                    <td><?= $reservation['car_name'] ?></td>
                    <td><?= $reservation['start_date'] ?></td>
                    <td><?= $reservation['end_date'] ?></td>
                    <td><?= $reservation['user_email'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                            <input type="hidden" name="action" value="delete_reservation">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Car Management</h2>
        <button onclick="toggleForm('addForm')" id="add_car_btn">Add New Car</button>

        <div id="addForm" class="form-section <?= $showAddForm ? 'visible' : '' ?>">
            <form method="post">
                <input type="hidden" name="action" value="add_car">
                <label>Brand: <input type="text" name="brand" required value="<?= $_POST['brand'] ?? '' ?>"></label>
                <span style="color:red;"> <?= $errors['brand'] ?? '' ?> </span><br>
                <label>Model: <input type="text" name="model" required value="<?= $_POST['model'] ?? '' ?>"></label>
                <span style="color:red;"> <?= $errors['model'] ?? '' ?> </span><br>
                <label>Year: <input type="number" name="year" required value="<?= $_POST['year'] ?? '' ?>"></label>
                <span style="color:red;"> <?= $errors['year'] ?? '' ?> </span><br>
                <label>Transmission:
                    <select name="transmission" required>
                        <option value="">Select Transmission</option>
                        <option value="Manual" <?= (isset($_POST['transmission']) && $_POST['transmission'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                        <option value="Automatic" <?= (isset($_POST['transmission']) && $_POST['transmission'] == 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                    </select>
                </label>
                <span style="color:red;"> <?= $errors['transmission'] ?? '' ?> </span><br>
                <label>Fuel Type:
                    <select name="fuel_type" required>
                        <option value="">Select Fuel Type</option>
                        <option value="Petrol" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Petrol') ? 'selected' : '' ?>>Petrol</option>
                        <option value="Electric" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Electric') ? 'selected' : '' ?>>Electric</option>
                        <option value="Diesel" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                    </select>
                </label>
                <span style="color:red;"> <?= $errors['fuel_type'] ?? '' ?> </span><br>
                <label>Passengers: <input type="number" name="passengers" required value="<?= $_POST['passengers'] ?? '' ?>"></label>
                <span style="color:red;"> <?= $errors['passengers'] ?? '' ?> </span><br>
                <label>Daily Price (HUF): <input type="number" name="daily_price_huf" required value="<?= $_POST['daily_price_huf'] ?? '' ?>"></label>
                <span style="color:red;"> <?= $errors['daily_price_huf'] ?? '' ?> </span><br>
                <label>Image URL: <input type="text" name="image" required value="<?= $_POST['image'] ?? '' ?>"></label>
                <span style="color:red;"> <?= $errors['image'] ?? '' ?> </span><br>
                <button type="submit">Add Car</button>
            </form>
        </div>

        <?php if ($editingCar): ?>
            <div id="editForm" class="form-section visible">
                <form method="post">
                    <input type="hidden" name="id" value="<?= $editingCar['id'] ?>">
                    <input type="hidden" name="action" value="save_car">
                    <label>Brand: <input type="text" name="brand" value="<?= $editingCar['brand'] ?>" required></label><br>
                    <label>Model: <input type="text" name="model" value="<?= $editingCar['model'] ?>" required></label><br>
                    <label>Year: <input type="number" name="year" value="<?= $editingCar['year'] ?>" required></label><br>
                    <label>Transmission:
                        <select name="transmission" required>
                            <option value="Manual" <?= ($editingCar['transmission'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                            <option value="Automatic" <?= ($editingCar['transmission'] == 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                        </select>
                    </label><br>
                    <label>Fuel Type:
                        <select name="fuel_type" required>
                            <option value="Petrol" <?= ($editingCar['fuel_type'] == 'Petrol') ? 'selected' : '' ?>>Petrol</option>
                            <option value="Electric" <?= ($editingCar['fuel_type'] == 'Electric') ? 'selected' : '' ?>>Electric</option>
                            <option value="Diesel" <?= ($editingCar['fuel_type'] == 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                        </select>
                    </label><br>
                    <label>Passengers: <input type="number" name="passengers" value="<?= $editingCar['passengers'] ?>" required></label><br>
                    <label>Daily Price (HUF): <input type="number" name="daily_price_huf" value="<?= $editingCar['daily_price_huf'] ?>" required></label><br>
                    <label>Image URL: <input type="text" name="image" value="<?= $editingCar['image'] ?>" required></label><br>
                    <button type="submit" id="save_btn">Save Changes</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $editingCar['id'] ?>">
                    <input type="hidden" name="action" value="cancel_edit">
                    <button type="submit">Cancel</button>
                </form>
            </div>
        <?php endif; ?>

        <h2>All Cars</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cars as $car): ?>
                <tr>
                    <td><?= $car['id'] ?></td>
                    <td><?= $car['brand'] ?></td>
                    <td><?= $car['model'] ?></td>
                    <td><?= $car['year'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $car['id'] ?>">
                            <input type="hidden" name="action" value="edit_car">
                            <button type="submit">Edit</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $car['id'] ?>">
                            <input type="hidden" name="action" value="delete_car">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>