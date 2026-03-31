<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flight\Flight;
use Users\Company;
use Users\Passenger;
use Users\User;

$userService = new User();
$passengerService = new Passenger();
$companyService = new Company();
$flightService = new Flight();

// 1) Register a passenger user
$passengerId = $userService->Register('Sara Ali', 'sara@example.com', 'secret123', '+20123456789', 'passenger');

if ($passengerId !== null) {
    $passengerService->addInfo($passengerId, 'uploads/sara.jpg', 'uploads/passport.jpg', '500.00');
}

// 2) Search available flights
$matchingFlights = $passengerService->searchFlight('Cairo', 'Dubai');

// 3) List company flights
$companyFlights = $flightService->list_flights(1);

// 4) Company reads booking messages
$companyMessages = $companyService->getMessages(1);

var_dump($matchingFlights, $companyFlights, $companyMessages);
