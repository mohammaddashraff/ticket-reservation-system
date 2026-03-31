# Engineering Decisions

## 1) Introduced `src/` Structure + PSR-4 Autoload
**Why:** Improves discoverability and scales better as features grow.

**Decision:** Organized code under:
- `src/Users`
- `src/Flight`
- `src/Connection`

Composer autoloading was added to support professional project ergonomics.

## 2) Kept Backward-Compatible Root Loaders
**Why:** Existing code may include root files directly (`User.php`, `Passenger.php`, etc.).

**Decision:** Root files are now compatibility loaders that include namespaced classes from `src/`.

## 3) Hardened SQL Access and Booking Logic
**Why:** Original code had multiple SQL interpolation points and inconsistent data operations.

**Decision:**
- Switched critical queries to prepared statements.
- Added transaction-aware booking updates in `makePurchase`.
- Reduced debug side effects and made method returns more predictable.

## 4) Added Environment-Driven DB Connection
**Why:** Keeps connection details out of source code and supports safer deployment.

**Decision:** `db_connection` reads settings from environment variables with sensible defaults.

## 5) Documented Scope Honestly
**Why:** Portfolio quality depends on clear boundaries and truthful claims.

**Decision:** Documentation explicitly states this repo is a backend domain/service layer, not a full HTTP app.
