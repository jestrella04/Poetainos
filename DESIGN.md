# Theme & Color System

## Overview

Poetaínos uses **Vuetify 4** with a **Material Design 3** color system generated from a single seed color, paired with EB Garamond (serif) and Karla (sans). The single source of truth for colors is `resources/js/plugins/theme.ts`; for fonts and other global overrides it's `resources/css/app.css`.

---

## Seed & Generation

| Seed | Hex | Scheme |
|---|---|---|
| Primary seed | `#176B73` | MD3 Tonal Spot (`@material/material-color-utilities`, `SchemeTonalSpot`, contrast level 0) |

The seed is only an input: the generated light primary (tone 40) is `#006971`, not `#176B73` itself. Values in `theme.ts` are literal hex generated once from the seed — the library is **not** a project dependency.

Only Vuetify's built-in token names are used (no `tertiary`, `*-container`, or `surface-container-*` tokens). MD3 roles are mapped onto them as follows.

---

## Token Reference

| Vuetify token | MD3 role | Light | Dark |
|---|---|---|---|
| `primary` / `on-primary` | primary / on-primary | `#006971` / `#ffffff` (6.4:1) | `#81d3dd` / `#00363b` (7.7:1) |
| `secondary` / `on-secondary` | secondary-container / on-secondary-container | `#cde7eb` / `#324b4e` (7.2:1) | `#324b4e` / `#cde7eb` (7.2:1) |
| `background`, `surface` | surface | `#f5fafb` | `#0e1415` |
| `on-background`, `on-surface` | on-surface | `#161d1d` (16.3:1) | `#dee4e4` (14.5:1) |
| `surface-bright` | surface-bright | `#f5fafb` | `#343a3b` |
| `surface-light` | surface-container-high | `#e3e9ea` | `#252b2c` |
| `surface-variant` | surface-variant | `#dae4e5` | `#3f484a` |
| `on-surface-variant` | on-surface-variant | `#3f484a` (8.9:1 on surface) | `#bec8c9` (10.9:1 on surface) |
| `error` / `on-error` | error / on-error | `#ba1a1a` / `#ffffff` (6.5:1) | `#ffb4ab` / `#690005` (7.7:1) |
| `success` / `on-success` | functional | `#1e6b3a` / `#ffffff` (6.5:1) | `#7fd99a` / `#00391c` (7.7:1) |
| `info` / `on-info` | functional | `#00658f` / `#ffffff` (6.4:1) | `#8ccdff` / `#00344d` (7.7:1) |
| `warning` / `on-warning` | functional | `#8a5100` / `#ffffff` (6.4:1) | `#ffb867` / `#4a2800` (7.7:1) |

Borders and dividers use MD3 **outline-variant** through Vuetify's `border-color` / `border-opacity` theme variables (`#bec8c9` light, `#3f484a` dark, opacity 1).

Primary also meets AA as text on `surface` (6.1:1 light, 10.9:1 dark), so it is safe for links and text buttons. `resources/js/plugins/__tests__/theme.test.ts` enforces ≥4.5:1 for every `on-*` / fill pair above.

### Why `secondary` maps to the container role

Vuetify has one `secondary` / `on-secondary` pair, and the app uses `color="secondary"` for quiet fills (avatars, award badges, category/tag chips). That is the job of MD3's secondary-container, not its solid secondary, so the container tones are used. If a solid secondary is ever needed, swap in MD3 secondary (`#4a6366` light / `#b1cbcf` dark) with its on-color.

### Functional colors

MD3 defines only `error`. `success`, `info` and `warning` are hue-neutral functional colors picked at MD3-style tones (40 light / 80 dark) so they keep ≥4.5:1 with their `on-*` colors.

---

## Fonts

Self-hosted via `@fontsource/eb-garamond` and `@fontsource/karla` (npm packages, bundled by Vite — no external request, precached by the PWA build), imported in `resources/js/app.ts`.

Wired into Vuetify via its own CSS custom-property hooks — no SASS rebuild needed. Vuetify's compiled CSS already reads `html { font-family: var(--v-font-body, "Roboto", ...) }` and all `text-h*`/`title-*`/`v-card-title`/etc. use `var(--v-font-heading, ...)`. Declared once in `resources/css/app.css`:

```css
:root {
  --v-font-body: 'Karla', system-ui, sans-serif;
  --v-font-heading: 'EB Garamond', Georgia, serif;
}
```

Body prose (writing text, comments, bios) lives in plain tags outside Vuetify's typography-class system, so it needs an explicit `.po-prose { font-family: var(--v-font-heading); }` class (also in `app.css`), applied where that prose appears.

`resources/css/app.css` also defines `.text-eyebrow { letter-spacing: 0.08em; }` for the small-caps-style uppercase labels used throughout (category+date lines, author bylines, section headers).

---

## How to Use Colors in Components

```vue
<!-- Vuetify color prop (preferred) -->
<v-btn color="primary">Guardar</v-btn>

<!-- CSS custom property -->
<div :style="{ borderColor: 'rgb(var(--v-theme-primary))' }"></div>

<!-- Utility class -->
<span class="text-on-surface-variant">Meta text</span>
```

Never use raw hex values in components. Never hard-code colors outside `theme.ts`.

---

## Updating Colors in the Future

1. Pick the new seed color (or keep the seed and adjust the roles).
2. Regenerate the Tonal Spot scheme with `@material/material-color-utilities` in a scratch directory (do not add it to `package.json`) for light and dark.
3. Map the roles onto Vuetify tokens exactly as in the table above and write the literal hex values into `theme.ts`.
4. Keep `resources/views/app.blade.php` (`theme-color` meta) and `resources/json/manifest.json` (`theme_color`, `background_color`) in sync with the light primary and surface.
5. Run the theme contrast test and update the values and ratios in this document.
