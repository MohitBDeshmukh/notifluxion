# Use Case Registry

## Implemented Use Cases
1. **Send Basic Email**: Resolves SMTP driver and shoots sync payload.
2. **Send Async Email**: Resolves DB Strategy, saves to `scheduled_notifications` with `status: pending`.
3. **CLI Installation**: Pushes config and migrations to the parent Laravel application via `notify:install`.

## Pending Use Cases
1. **WhatsApp Twilio Adapter**: Combine WhatsApp and Twilio integrations.
2. **Redis Queue Strategy**: Implements native Laravel Horizon queues instead of polling database tables.
3. **Multi-Tenant Scoping**: Isolates notification logs by `tenant_id`.

## Deprecated Flows
- None.
