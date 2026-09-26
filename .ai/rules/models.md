---
paths:
  - 'app/Models/**'
---

# Models

## Mass assignment via $fillable
Use `protected $fillable` allow-lists on models exposed to mass assignment. Never use `$guarded`.

## No schema-less JSON columns for model data
Store model data in real columns or relations: profile fields live on `UserProfile` (read flat via `User::withProfileFields()`), account settings are columns on `users`, linked OAuth providers are `SocialAccount` rows, writing cover/link are columns. Don't add keys to the legacy `extra_info` JSON; it only remains until it is dropped.
