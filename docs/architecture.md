# Architecture Document

## System Design
The Notification Library is designed on a driver-based architecture using Laravel's core `Manager` pattern.

- `NotificationManager`: Resolves strategies and channels, central routing dispatcher.
- `QueueStrategyInterface`: Handles execution paths:
  - `SyncQueueStrategy`: Executes inline.
  - `DatabaseQueueStrategy`: Writes to `scheduled_notifications` for async processing.
- `DriverInterface`: Implemented by `SmtpDriver`, `TwilioDriver`, etc.

## Driver Lifecycle
1. Payload sent via `Notify::send($user, $notification)`.
2. Manager checks active queue strategy config (`notify.queue.strategy`).
3. Payload is serialized and passed to strategy via `$strategy->push()`.
4. Strategy either resolves the `DriverInterface` immediately (Sync) or saves and resolves later (Database/Redis).
5. Driver parses the serialized Notification formatting logic (e.g. `toMail`, `toTwilio` methods) and dispatches it via Provider's native SDK.
