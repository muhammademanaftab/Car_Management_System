<?php
session_start();
require_once 'storage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$car_id = $_POST['car_id'] ?? null;
$from_date = $_POST['from_date'] ?? '';
$until_date = $_POST['until_date'] ?? '';
$user_email = $_SESSION['user']['email'] ?? null;

if (!$car_id || !$from_date || !$until_date || !$user_email) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

$reservationStorage = new Storage(new JsonIO('reservations.json'));
$existing_reservations = $reservationStorage->findAll(['car_id' => $car_id]);

$today = date('Y-m-d');
$errors = [];

if (!strtotime($from_date) || $from_date < $today) {
    $errors[] = "The 'From' date must be today or later";
}
if (!strtotime($until_date) || $until_date <= $from_date) {
    $errors[] = "The 'Until' date must be after the 'From' date";
}

foreach ($existing_reservations as $reservation) {
    if (
        ($from_date >= $reservation['start_date'] && $from_date <= $reservation['end_date']) ||
        ($until_date >= $reservation['start_date'] && $until_date <= $reservation['end_date']) ||
        ($from_date <= $reservation['start_date'] && $until_date >= $reservation['end_date'])
    ) {
        $errors[] = "The car is already booked for the selected dates";
        break;
    }
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode('. ', $errors)
    ]);
    exit();
}

$carStorage = new Storage(new JsonIO('cars.json'));
$car = $carStorage->findById($car_id);

$reservation = [
    'car_id' => $car['id'],
    'car_name' => $car['brand'] . ' ' . $car['model'],
    'car_image' => $car['image'],
    'start_date' => $from_date,
    'end_date' => $until_date,
    'user_email' => $user_email
];

$reservationStorage->add($reservation);

echo json_encode([
    'success' => true,
    'message' => "Your booking has been successfully confirmed!",
    'details' => $reservation
]);