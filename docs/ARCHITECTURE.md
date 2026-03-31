# Architecture

## Scope
This repository contains the **backend domain layer** for a ticket reservation system.
It focuses on data operations and business logic for users, passengers, companies, flights, and ticket booking.

## High-Level Components

- `src/Connection/db_connection.php`
  - Creates and manages MySQL connections using environment variables.
- `src/Users/User.php`
  - Account registration and authentication.
- `src/Users/Passenger.php`
  - Passenger profile management, flight search, booking, and purchase operations.
- `src/Users/Company.php`
  - Company profile management and message retrieval.
- `src/Flight/Flight.php`
  - Flight creation and listing.

## Data Model (Inferred)
Main tables used by the code:

- `users`
- `passengers`
- `companies`
- `flights`
- `passengersOnFlight`
- `messages`

See [examples/schema.sql](../examples/schema.sql) for a starter schema inferred from the codebase.

## Booking Flow

1. Passenger searches for flights by itinerary.
2. Passenger chooses payment type (`account` or `cash`).
3. System creates booking in `passengersOnFlight`.
4. System updates company balance.
5. System decrements available seats.
6. System stores a booking message for company visibility.
7. If payment is from passenger account, passenger balance is decremented.

## Transaction Handling
`Passenger::makePurchase` uses database transactions to keep booking side-effects consistent.
If one critical update fails, all changes in that transaction are rolled back.

## Notes
- This repository currently exposes class-level APIs, not HTTP endpoints.
- Integration into a framework/controller layer can be done by wiring these classes into routes or service handlers.
