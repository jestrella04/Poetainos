---
paths:
  - 'database/migrations/**'
---

# Migrations

## Manual foreign key definitions
Define foreign keys with `$table->foreign('x_id')->references('id')->on('table')->onDelete('CASCADE')`, not `foreignId()`/`foreignIdFor()`.
