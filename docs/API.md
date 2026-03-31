# API Reference (Class Methods)

This project exposes a class-based API.

## `Users\\User`

| Method | Purpose | Returns |
|---|---|---|
| `Register($name, $email, $password, $tel, $type)` | Creates a new user account (with hashed password). | `int|null` (user ID or `null` if email exists/failure) |
| `signIn($email, $password)` | Authenticates a user. | `array{id:int,type:string}|null` |

## `Users\\Passenger`

| Method | Purpose | Returns |
|---|---|---|
| `addInfo($user_id, $photo, $passportImg, $account)` | Creates passenger profile info. | `bool` |
| `getAllInfo($user_id)` | Retrieves passenger + user profile details. | `array` |
| `editData($user_id, $name, $email, $password, $tel, $photo, $passportImg, $account)` | Updates user and passenger profile fields. | `bool` |
| `listCompletedFlights($user_id)` | Lists completed flights for passenger. | `array|string` |
| `currentFlights($user_id)` | Lists active flights for passenger. | `array|string` |
| `searchFlight($from, $to)` | Searches flights by itinerary segments. | `array` |
| `makePurchase($passengerId, $flightId, $paymentType)` | Books a ticket and updates balances/seats/messages. | `bool` |

## `Users\\Company`

| Method | Purpose | Returns |
|---|---|---|
| `registerInfo($companyId, $companyName, $bio, $address, $location, $logoImg)` | Creates company profile data. | `bool` |
| `getInfo($companyId)` | Fetches company profile. | `array|null` |
| `updateInfo($companyId, $companyName, $bio, $address, $location, $logoImg)` | Updates company profile, optionally replacing logo. | `bool` |
| `getMessages($user_id)` | Retrieves messages related to company bookings. | `array` |

## `Flight\\Flight`

| Method | Purpose | Returns |
|---|---|---|
| `add_flight($name, $itinerary, $fees, $start_date, $end_date, $completed, $companyId, $numPassengers)` | Creates a flight record. | `bool` |
| `list_flight($flightid)` | Fetches one flight by ID. | `array|null` |
| `list_flights($companyID)` | Lists all flights for a company. | `array` |

## Example
See [examples/quick_start.php](../examples/quick_start.php) for a minimal usage example.
