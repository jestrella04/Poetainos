---
paths:
  - 'app/Notifications/**'
---

# Notifications

## Broadcast notification via event() helper
Dispatch `NotificationEvent` with the global `event()` helper from a Notification's delivery method, not `::dispatch()`. Model lifecycle hooks (`booted()`, observers) are not used for this.
