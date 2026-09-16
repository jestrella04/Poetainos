# Theme & Color System

## Overview

Poetaínos uses **Vuetify 4** with a color system derived from a Claude Design mockup (`.claude/redesign/Poetainos Rediseño.dc.html`): an editorial, paper-like palette (greenish-gray surfaces, plum/mauve accent) paired with EB Garamond (serif) and Karla (sans). The single source of truth for colors is `resources/js/plugins/theme.ts`; for fonts and other global overrides it's `resources/css/app.css`. All color decisions should trace back to the mockup's anchors or a documented rationale below.

---

## Brand Anchors

| Anchor | Hex | Role |
|---|---|---|
| Primary | `#8e5686` | Plum/mauve accent — buttons, links, active tabs |
| Primary hover/darken | `#6f3f68` | Hover/pressed state |
| Paper | `#f2f3ef` | The only page tone — used for both `background` and `surface` |

**Note:** an earlier pass also pulled `#dfe1dc` out of the mockup as a second "outer canvas" tone and mapped it to `background` (distinct from `surface`). That was wrong: `#dfe1dc` is only the `<body>` background of the Claude Design *canvas document* itself (`.claude/redesign/Poetainos Rediseño.dc.html`), i.e. the backdrop behind the four separate artboards in that file — not a color used by any actual screen. Every real screen ("Inicio", "Lectura", "Explorar", "Perfil") backgrounds itself with `#f2f3ef`. There is no second page tone in the design; cards are separated by borders/shadow, not a background shift, so `background` and `surface` share the same value.

---

## Token Reference

### Light theme

| Token | Value | Notes |
|---|---|---|
| `background` | `#f2f3ef` | the page's only tone — same as `surface` |
| `on-background` | `#1e221d` | |
| `surface` | `#f2f3ef` | cards/panels ("papers") |
| `on-surface` | `#1e221d` | ≈15.5:1 on surface (AAA) |
| `surface-variant` | `#e4e6e0` | thin borders/dividers, subtle fills |
| `on-surface-variant` | `#5a6057` | ≈4.9:1 on surface (AA) — eyebrow labels, meta text, captions |
| `primary` | `#8e5686` | |
| `on-primary` | `#f7f2f6` | ≈5.4:1 on primary (AA) |
| `primary-darken-1` | `#6f3f68` | ≈8.1:1 on white (AAA) — hover/pressed |
| `secondary` | `#dfe2da` | **neutral** grey-green (not a second brand hue) — avatar/chip fills |
| `on-secondary` | `#5a6057` | ≈4.9:1 (AA) |
| `secondary-darken-1` | `#cdd2c7` | hover state for neutral chips/buttons |
| `success` | `#059669` | functional, hue-neutral |
| `info` | `#0284C7` | functional |
| `warning` | `#D97706` | functional |
| `error` | `#DC2626` | functional |

### Dark theme (derived)

| Token | Value | Notes |
|---|---|---|
| `background` | `#23261f` | the page's only tone — same as `surface`; borders separate cards, not elevation |
| `on-background` | `#e7e8e3` | |
| `surface` | `#23261f` | same value as `background` |
| `on-surface` | `#e7e8e3` | ≈14:1 on surface |
| `surface-variant` | `#33362d` | dark borders/dividers |
| `on-surface-variant` | `#9aa093` | muted meta text on dark |
| `primary` | `#c48fba` | lightened/desaturated plum, ≈7.1:1 on background |
| `on-primary` | `#2a1027` | ≈6.9:1 on primary |
| `primary-darken-1` | `#8e5686` | reuses the light theme's primary as the dark "darken" step |
| `secondary` | `#33362d` | dark neutral avatar/chip fill |
| `on-secondary` | `#c7ccc0` | ≈10:1 |
| `success` / `info` / `warning` / `error` | `#34D399` / `#38BDF8` / `#FBBF24` / `#F87171` | functional, unchanged in spirit from light |

### Why `secondary` is neutral, not a second brand hue

The mockup's avatar circles and default chip fills are a neutral grey-green (`#dfe2da` bg, `#5a6057` text), not a saturated accent. Since the codebase already uses `color="secondary"` pervasively for avatars, award badges, and category/tag chips, `secondary` is mapped to this neutral pairing so all existing usages read as "neutral" without per-call-site changes.

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

## Component defaults

Flat, bordered "paper" look instead of Material shadows, set once in `resources/js/plugins/vuetify.ts`'s `defaults` block: `VCard`/`VBtn`/`VChip` at `rounded: 0, elevation: 0` (`VCard` also gets `border: true`), `VTextField` underlined, `VDivider` colored `surface-variant`. Component-wide look changes belong here, not as scattered per-instance props — check this file before adding new one-off styling.

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

1. Get the new brand anchors (minimum: primary, a paper background/surface pair).
2. Assign tones to roles the same way as above (primary/on-primary/darken-1, background/surface pairs, a neutral `secondary` unless a real second accent is provided).
3. Derive a dark counterpart in the same spirit: darken backgrounds, lighten/desaturate the accent enough to hit ~7:1+ contrast on the new dark background.
4. Update `theme.ts` and this document together.
