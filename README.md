# WaarneemApp Backend Challenge

API for analyzing a network of doctors. Given a starting doctor and a target specialization, find all reachable doctors in their network (traversing only through nodes with the target specialization) and aggregate their profiles.

## Requirements

- PHP 8.3+
- Composer
- SQLite (php-sqlite3 extension)
- Laravel 13

## Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the database:
   ```bash
   cp assignment.db database/database.sqlite
   ```
4. Copy environment file:
   ```bash
   cp .env.example .env
   ```
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Start the server:
   ```bash
   php artisan serve
   ```

## API

### Network Aggregates

```
GET /doctor/network-aggregates/{id}?specialization={name}
```

**Parameters:**
- `id` (required) - Doctor ID
- `specialization` (required) - Target specialization name (e.g., "Surgery")
- `min_yoe` (optional) - Minimum years of experience filter
- `max_yoe` (optional) - Maximum years of experience filter

**Examples:**

```bash
# Get specialization aggregates for reachable surgeons from doctor 56
curl -H "Accept: application/json" -X GET \
  "http://127.0.0.1:8000/doctor/network-aggregates/56?specialization=Surgery"

# With years of experience filter
curl -H "Accept: application/json" -X GET \
  "http://127.0.0.1:8000/doctor/network-aggregates/56?specialization=Surgery&min_yoe=3&max_yoe=10"
```

**Response (basic):**
```json
{
    "specializations_aggregrates": {
        "Allergy and immunology": 15,
        "Anesthesiology": 9,
        "Cardiology": 14,
        "Surgery": 41
    }
}
```

**Response (with yoe filter):**
```json
{
    "specializations_aggregrates": {
        "Allergy and immunology": 7,
        "Anesthesiology": 3,
        "Cardiology": 2,
        "Surgery": 17
    },
    "years_of_experience_aggregates": {
        "3": 4,
        "4": 3,
        "5": 4,
        "6": 1,
        "7": 2,
        "8": 1,
        "10": 2
    }
}
```
