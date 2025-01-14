<?php
session_start();

require_once 'storage.php';

if (!isset($_GET['car_id']) || empty($_GET['car_id'])) {
    // taking the car id from t...
    http_response_code(400);

    echo json_encode(['error' => 'Car ID is required']);
    exit();
}

$car_id = $_GET['car_id'];

$reservationStorage = new Storage(new JsonIO('reservations.json'));

$reservations = $reservationStorage->findAll(['car_id' => $car_id]);

if (!$reservations) {
    $reservations = [];
}

header('Content-Type: application/json');

echo json_encode($reservations, JSON_PRETTY_PRINT);
