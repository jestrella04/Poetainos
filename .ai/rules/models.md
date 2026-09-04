---
paths:
  - 'app/Models/**'
---

# Models

## Mass assignment via $fillable
Use `protected $fillable` allow-lists on models exposed to mass assignment. Never use `$guarded`.
