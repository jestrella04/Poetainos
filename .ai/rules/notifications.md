---
paths:
  - 'app/Notifications/**'
---

# Notifications

## Live notifications use the native broadcast channel
List `'broadcast'` after `'database'` in `via()`, so the unread count includes the new notification. `PoetainosNotification::toBroadcast()` returns a `BroadcastMessage` with the recipient's unread count, which the frontend receives with Echo `.private('App.Models.User.' + id).notification()`. Never dispatch events from inside a delivery method.
