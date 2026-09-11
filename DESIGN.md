# Theme & Color System

## Overview

Encolame uses **Vuetify 4** with a fully custom MD3-aligned color system. The single source of truth is `resources/js/plugins/theme.ts`. All color decisions must trace back to the designer-provided brand anchors or a documented rationale.

---

## Brand Primary Palette

Three official anchors define the MD3 tonal palette at **hue ≈267° (blue-violet)**:

| Anchor | Hex | MD3 Tone | Role |
|---|---|---|---|
| Primary dark | `#633CAF` | ~40 | Light mode `primary`; `primary-darken-1` in dark mode |
| Primary neutral | `#8C57FF` | ~60 | Dark mode `primary`; Inertia progress bar; gradients |
| Primary light | `#F4F0FF` | ~95 | Light mode `surface`; containers; light backgrounds |

### Why three anchors instead of one?

MD3 generates a full tonal palette (tones 0–100) from a single seed color. The designer provided three explicit tones from that palette, which gives us exact reference points without needing to run the generation tooling. Future derivations should interpolate/extrapolate within this range or use [Material Theme Builder](https://m3.material.io/theme-builder) with `#633CAF` as the seed.

---

## MD3 Mode Assignment

### Light mode
- `primary` = tone 40 → `#633CAF`
- White (`#FFFFFF`) on `#633CAF` achieves **~7.3:1 contrast** (WCAG AA + AAA) ✓
- Surfaces use tone 95 (`#F4F0FF`) — a very light lavender that maintains brand identity without competing with primary

### Dark mode
- `primary` = tone 60 → `#8C57FF` (brighter than tone 40, readable on dark backgrounds)
- `on-primary` = tone ~10 → `#21005E` (dark violet text on the bright purple)
  - **Why not white?** White on `#8C57FF` only achieves ~4.1:1 — marginal for normal text. Dark text gives ~19:1.
- This is the canonical MD3 pattern: as the surface darkens, primary shifts to a higher (lighter) tone

---

## Derived Tones

Values computed by linear interpolation between the three anchors:

| Value | Hex | Usage |
|---|---|---|
| Tone ~30 | `#4E2D97` | `primary-darken-1` (light mode); glass gradient mid-point |
| Tone ~10 | `#1A0050` | `staticGlassDarkColor` — deepest dark in gradients |
| Neutral-variant ~10 | `#1D1040` | `on-background`, `on-surface`, `grey-900` in light mode |

---

## Complete Token Reference

### Static exports (`theme.ts` lines 3–5)

These are used by components that need raw hex values outside Vuetify's theme system (progress bar, charts).

| Export | Value | Consumer |
|---|---|---|
| `staticPrimaryColor` | `#8C57FF` | Inertia page progress bar (`app.ts`); chart primary series (`useChartTheme.ts`) |
| `staticPrimaryDarkColor` | `#633CAF` | Available for gradients and darken-state references |
| `staticPrimaryLightColor` | `#F4F0FF` | Available for light surface and container references |

### Light theme

#### Primary family

| Token | Value | Notes |
|---|---|---|
| `primary` | `#633CAF` | Tone 40 — all primary interactive elements |
| `on-primary` | `#FFFFFF` | 7.3:1 contrast ✓ |
| `primary-darken-1` | `#4E2D97` | Tone ~30 — hover/pressed states |

#### Neutral surfaces

| Token | Value | Notes |
|---|---|---|
| `background` | `#E2D4F0` | Neutral-variant tone ~85 — page-level background |
| `on-background` | `#1D1040` | Neutral-variant tone ~10 |
| `surface` | `#F4F0FF` | Brand primary light (tone 95) — cards, dialogs, inputs |
| `on-surface` | `#1D1040` | Same as on-background |

#### Grey scale (neutral-variant, hue ≈267°)

| Token | Value |
|---|---|
| `grey-50` | `#FAF9FF` |
| `grey-100` | `#F5F0FF` |
| `grey-200` | `#EDE5FF` |
| `grey-300` | `#D9CCFF` |
| `grey-400` | `#B5A5F0` |
| `grey-500` | `#8E79D4` |
| `grey-600` | `#6E5AB0` |
| `grey-700` | `#503D85` |
| `grey-800` | `#352860` |
| `grey-900` | `#1D1040` |

#### UI alias tokens (light)

| Token | Value | Follows |
|---|---|---|
| `perfect-scrollbar-thumb` | `#D9CCFF` | `grey-300` |
| `track-bg` | `#EDE5FF` | `grey-200` |
| `chat-bg` | `#F5F0FF` | `grey-100` |
| `expansion-panel-text-custom-bg` | `#FAF9FF` | `grey-50` |
| `skin-bordered-background` | `#FFFFFF` | Pure white |
| `skin-bordered-surface` | `#FFFFFF` | Pure white |

#### Variables (light)

| Variable | Value |
|---|---|
| `code-color` | `#633CAF` |
| `overlay-scrim-background` | `#1D1040` |
| `tooltip-background` | `#1D1040` |
| `border-color` | `#1D1040` |
| `table-header-color` | `#F5F0FF` |
| `shadow-key-umbra-color` | `#1D1040` |

### Dark theme

#### Primary family

| Token | Value | Notes |
|---|---|---|
| `primary` | `#8C57FF` | Tone 60 — bright enough on dark backgrounds |
| `on-primary` | `#21005E` | Tone ~10 — ~19:1 contrast ✓ |
| `primary-darken-1` | `#633CAF` | Tone 40 — darken state in dark mode |

#### Neutral surfaces

| Token | Value | Notes |
|---|---|---|
| `background` | `#0A0618` | Very dark, near-black with purple tint |
| `on-background` | `#EDE5FF` | Tone ~93 light lavender |
| `surface` | `#181229` | Dark card/dialog surface (tone ~12 — intentionally elevated to "Surface Container" for visual lift; slightly above strict MD3 tone 6) |
| `on-surface` | `#EDE5FF` | Same as on-background |

#### Grey scale (dark, hue ≈267°)

| Token | Value |
|---|---|
| `grey-50` | `#130B30` |
| `grey-100` | `#1A1040` |
| `grey-200` | `#231660` |
| `grey-300` | `#2D2070` |
| `grey-400` | `#3B3380` |
| `grey-500` | `#5C52A8` |
| `grey-600` | `#8A7FD4` |
| `grey-700` | `#ACA2E8` |
| `grey-800` | `#CBC0F8` |
| `grey-900` | `#EDE5FF` |

#### UI alias tokens (dark)

| Token | Value | Follows |
|---|---|---|
| `perfect-scrollbar-thumb` | `#2D2070` | `grey-300` |
| `skin-bordered-background` | `#130B30` | `grey-50` |
| `skin-bordered-surface` | `#130B30` | `grey-50` |
| `track-bg` | `#231660` | `grey-200` |
| `expansion-panel-text-custom-bg` | `#1A1040` | `grey-100` |
| `chat-bg` | `#1A1040` | `grey-100` |

#### Variables (dark)

| Variable | Value |
|---|---|
| `code-color` | `#C084FC` |
| `overlay-scrim-background` | `#080810` |
| `tooltip-background` | `#EDE5FF` |
| `border-color` | `#EDE5FF` |
| `table-header-color` | `#1A1040` |
| `shadow-key-umbra-color` | `#000000` |

### Semantic / functional colors (unchanged by brand)

These are not brand colors — they're functional. Do not derive them from the primary palette.

| Token | Light | Dark |
|---|---|---|
| `secondary` | `#9333EA` | `#9333EA` |
| `secondary-darken-1` | `#7E22CE` | `#7E22CE` |
| `success` | `#059669` | `#34D399` |
| `info` | `#0284C7` | `#38BDF8` |
| `warning` | `#D97706` | `#FBBF24` |
| `error` | `#DC2626` | `#F87171` |

Secondary has not been updated — no secondary tonal palette was provided by the designer. It sits at a similar hue family (~280°) and reads well alongside the new primary. Revisit when the designer delivers a full palette.

---

## How to Use Colors in Components

```vue
<!-- Vuetify color prop (preferred) -->
<v-btn color="primary">Save</v-btn>

<!-- CSS custom property -->
<div :style="{ borderColor: 'rgb(var(--v-theme-primary))' }"></div>

<!-- Utility class -->
<span class="text-primary">Label</span>

<!-- Static export (only when outside Vuetify's theme system) -->
import { staticPrimaryColor } from '@/plugins/theme';
```

Never use raw hex values in components. Never hard-code colors outside `theme.ts`.

---

## Updating Colors in the Future

1. Get the designer's new anchor colors (minimum: primary dark, primary neutral, primary light)
2. Identify the MD3 tones they map to using [Material Theme Builder](https://m3.material.io/theme-builder) or run:
   ```js
   import { Hct, argbFromHex } from '@material/material-color-utilities';
   const hct = Hct.fromInt(argbFromHex('#yourColor'));
   console.log(hct.tone); // MD3 tone value
   ```
3. Assign tones to roles (light primary = tone 40, dark primary = tone 60–80, surface = tone 95)
4. Derive the neutral-variant grey scale by taking the same hue at chroma ~16
5. Update `theme.ts` and this document together
