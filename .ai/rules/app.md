---
paths:
  - 'app/**/*.php'
---

# App

## Prefer global helper functions over facades
Use `config()`/`auth()`/`request()`/`response()` etc. over `Config::`/`Auth::` facades. App-specific procedural utilities go in `app/Helpers/Helper.php` as global functions.

## Idempotent writes via firstOrCreate
Use `firstOrCreate()` for get-or-create writes. `updateOrCreate()`/`upsert()` are not used in this codebase.

## Prefer native PHP string functions
Use native functions (`trim`, `sprintf`, `str_replace`, etc.) for string manipulation. Reserve `Str::` for slug/random/transliterate-type helpers without native equivalents.

## Use Carbon:: static calls for date construction
Construct dates with `Carbon::now()`/`Carbon::today()`/`Carbon::parse()` rather than the `now()`/`today()` helpers.

## PHP translations use full-sentence keys
Call `__('Full English sentence', [...])` with the literal sentence as the key. Translate in `lang/poetainos/es.json`.
