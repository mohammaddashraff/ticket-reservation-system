# Ticket Reservation System

**A clean PHP/MySQL backend domain layer for multi-role flight ticket booking workflows.**

This repository focuses on the service/business-logic layer of a reservation platform and demonstrates practical backend concerns: profile management, flight management, search, booking, payment modes, and transactional consistency.

## Overview
This project models a ticket reservation workflow with three core roles:

- **User**: authentication and account registration
- **Passenger**: profile, search, booking, purchase
- **Company**: profile, flight ownership, booking notifications

It is intentionally implemented as a class-based backend module, so it can be integrated into a framework, API controllers, or a larger monolith.

## Quick Start

```bash
git clone <your-repo-url>
cd ticket-reservation-system
cp .env.example .env
composer install
composer dump-autoload
mysql -u <db_user> -p < examples/schema.sql
composer run lint
```

Then integrate classes from `src/` into your controller/router layer.

## Main Features

- User registration and sign-in
- Passenger profile creation and updates
- Company profile creation and updates
- Flight creation and listing by company
- Flight search by itinerary segments
- Passenger booking with two payment modes:
  - account balance
  - cash
- Transaction-aware purchase flow that updates:
  - booking records
  - available seats
  - company account
  - passenger account (for account payments)
  - company messages

## Tech Stack

| Area | Technology |
|---|---|
| Language | PHP 8 |
| Database | MySQL / MariaDB |
| Data Access | `mysqli` + prepared statements |
| Dependency Management | Composer (PSR-4 autoload) |

## Project Structure

```text
ticket-reservation-system/
├── src/
│   ├── Connection/
│   │   └── db_connection.php
│   ├── Flight/
│   │   └── Flight.php
│   └── Users/
│       ├── Company.php
│       ├── Passenger.php
│       └── User.php
├── docs/
│   ├── API.md
│   ├── ARCHITECTURE.md
│   ├── DECISIONS.md
│   └── SETUP.md
├── examples/
│   ├── quick_start.php
│   └── schema.sql
├── screenshots/
│   └── README.md
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

## Environment Variables

| Variable | Description | Default |
|---|---|---|
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_NAME` | Database name | `ticket_reservation` |
| `DB_USER` | Database username | `root` |
| `DB_PASSWORD` | Database password | _(empty)_ |
| `DB_CHARSET` | MySQL charset | `utf8mb4` |

## Usage Example

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Users\Passenger;

$passengerService = new Passenger();
$flights = $passengerService->searchFlight('Cairo', 'Dubai');
```

See [examples/quick_start.php](examples/quick_start.php) for a fuller example.

## API / Architecture Docs

- [Setup Guide](docs/SETUP.md)
- [Architecture Notes](docs/ARCHITECTURE.md)
- [Class API Reference](docs/API.md)
- [Engineering Decisions](docs/DECISIONS.md)

## Why This Project Is Notable

- Demonstrates **domain-driven backend organization** without framework lock-in
- Implements a **transactional booking flow** touching multiple related entities
- Shows **incremental hardening** of legacy-style PHP into cleaner, maintainable code
- Ready to be wired into REST/GraphQL endpoints or a web framework

## Screenshots / Demo

Screenshots are not committed yet.
Use [screenshots/README.md](screenshots/README.md) as a checklist for what to add.

## Challenges and Solutions

- **Challenge:** Multiple SQL statements had interpolation risk and inconsistent patterns.
  **Solution:** Migrated critical operations to prepared statements and clearer query boundaries.
- **Challenge:** Booking operation updates several tables and can leave inconsistent state on failure.
  **Solution:** Added transaction-based handling in purchase flow.
- **Challenge:** Repository lacked discoverability for recruiters.
  **Solution:** Introduced structured docs, setup assets, and cleaner source layout.

## Future Improvements

- Add PHPUnit tests for each service method and booking edge cases
- Expose this module via REST endpoints (e.g., Slim/Laravel/Symfony)
- Add migration tooling (Phinx/Laravel migrations)
- Add CI checks (lint + tests) via GitHub Actions
- Introduce interfaces/repositories for easier mocking and testability

## Author

**Mohammad Ashraf**

## License

No open-source license has been added yet. This means default copyright applies.
If you want public reuse, add a license file (for example MIT).
