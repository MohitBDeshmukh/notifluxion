# Test Case Registry

This registry tracks the core scenarios executed by the automated Pest/PHPUnit test suite aligning with our implemented Use Cases natively.

## 1. Notification Flow Tests (`NotificationFlowTest.php`)
These cover **Use Case #1 (Send Basic Email)** and **Use Case #2 (Send Async Email)**.

| Scenario | Expected Result | Status |
|---|---|---|
| **Dispatches notification using sync strategy** | Should instantly route the payload directly to the generated driver and return successful execution without hitting the DataBase. | ✅ PASS |
| **Dispatches notification using database strategy** | Should intercept the dispatch and insert a `pending` row into `scheduled_notifications` containing the serialized payload. | ✅ PASS |
| **Database strategy can process queue** | Running `$strategy->process()` should fetch `pending` jobs, execute them, and instantly update status from `pending` to something else (e.g. `completed`). | ✅ PASS |

## 2. Integration / DB Tests (`DatabaseIntegrationTest.php`)
These cover foundation requirements for package functionality.

| Scenario | Expected Result | Status |
|---|---|---|
| **Database tables are created successfully** | Migrations for all 3 schema tables (`notifications`, `notification_logs`, `scheduled_notifications`) bind to MySQL and execute without errors. | ✅ PASS |
| **Can insert into scheduled notifications** | Validates the Database Schema matches Laravel's Query Builder syntax on MySQL insertions strictly. | ✅ PASS |

## 3. CLI Tests (`NotificationCommandTest.php`)
These cover **Use Case #3 (CLI Installation)**.

| Scenario | Expected Result | Status |
|---|---|---|
| **Install command runs successfully** | `php artisan notify:install` should correctly provide CLI output menus and attempt to publish config/migrations natively. | ✅ PASS |
