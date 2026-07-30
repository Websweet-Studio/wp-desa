---
name: "hp-design-analysis"
description: "WP-Desa admin design system — white-paper enterprise UI anchored by HP Electric Blue (#024ad8) primary CTA, near-black ink (#1a1a1a) text, 4px/16px two-tier radius, 8px spacing grid, and inline SVG Lucide icons. Use when building admin UI matching WP-Desa's residents/finances/settings page patterns."
---

# WP-Desa Admin Design System

Based on the wp-desa-residents page (list + form views), wp-desa-keuangan, wp-desa-pemerintahan, and other admin pages. A white-paper admin UI with a single blue CTA, sharp 4px interactive elements, soft 16px cards, and consistent 8px-base spacing.

## Colors

### Brand & Accent

- **Primary Blue** (`--primary` — `#024ad8`): the lone signal — primary button fill, link color, active nav indicator, stat card icon backgrounds. Used sparingly.
- **Primary Bright** (`--primary-bright` — `#296ef9`): hover/lighter variant for interactive states.
- **Primary Deep** (`--primary-deep` — `#0e3191`): pressed state for primary CTA.
- **Primary Soft** (`--primary-soft` — `#c9e0fc`): pale blue used for stat card icon containers.

### Surface

- **Canvas** (`--canvas` — `#ffffff`): universal page and card background. White, full opacity.
- **Cloud** (`--cloud` — `#f7f7f7`): alternate row background, pagination bar background, modal footer, form actions footer, stat card value backgrounds.
- **Fog** (`--fog` — `#e8e8e8`): hairline borders for cards, table cells, input borders, subnav separators, pagination top border.
- **Steel** (`--steel` — `#c2c2c2`): default input border color, disabled state fills.

### Text

- **Ink** (`--ink` — `#1a1a1a`): universal text — headlines, body, table content, button labels, nav links.
- **Charcoal** (`--charcoal` — `#3d3d3d`): muted body text, helper text, secondary descriptions.
- **Graphite** (`--graphite` — `#636363`): small print, table header labels (uppercase), placeholder text, pagination info, empty state text.
- **On Ink** (`--on-ink` — `#ffffff`): white text on dark backgrounds (hero band, primary button, dark subnav tab).

### Semantic

- **Error** (`--error` — `#b3262b`): error messages, danger badges, required field asterisk (`wp-desa-req`), danger outline button text/border.
- **Success** (`--success` — `#1f6b3c`): success badges (completed, resolved, income), income bar fill, green text utility.
- **Warning** (`--warning` — `#9a5b1e`): warning badges (in_progress, processed).

### Special

- **Bloom Rose** (`--bloom-rose` — `#f9d4d2`): danger outline button background, danger button background.
- **Bloom Deep** (`--bloom-deep` — `#b3262b`): danger text, same as error.
- **Bloom Coral** (`--bloom-coral` — `#ff5050`): pending list dot indicator.

## Typography

### Font Family

Single family across all surfaces: **Forma DJR Micro** (HP's bespoke geometric grotesque), with fallback stack: Manrope, Inter, Arial, ui-sans-serif, sans-serif.

Applied via two CSS variables:

- `--font-display` — for section titles, stat values, hero titles, modal titles, page titles (weight 500)
- `--font-body` — for everything else: body text, table content, inputs, buttons, labels

### Hierarchy

| Token             | Size   | Weight | Line Height | Letter Spacing | Use                                                |
| ----------------- | ------ | ------ | ----------- | -------------- | -------------------------------------------------- |
| `hero-title`      | 44px   | 500    | 1.0         | 0              | Dashboard hero headline                            |
| `hero-value`      | 32px   | 500    | 1.0         | 0              | Stat metric values                                 |
| `page-title`      | 32px   | 500    | 1.0         | 0              | Page heading (`wp-desa-title`)                     |
| `section-title`   | 20px   | 500    | 1.0         | 0              | Card titles, modal titles, section headings        |
| `body-default`    | 16px   | 400    | 1.38        | 0              | Form inputs, body text                             |
| `body-emphasis`   | 16px   | 500    | 1.38        | 0              | Bold body runs                                     |
| `body-small`      | 14px   | 400    | 1.5         | 0              | Helper text, info details, table cells             |
| `label-bold`      | 14px   | 500    | 1.0         | 0              | Form labels (`wp-desa-label`), sidebar titles      |
| `uppercase-label` | 12px   | 600    | 1.0         | 0.7px          | Table header labels, stat card titles, info labels |
| `button-label`    | 14px   | 600    | 1.4         | 0.7px          | Button labels (uppercase)                          |
| `button-sm`       | 12.6px | 700    | 1.0         | 0.126px        | Small button labels (uppercase)                    |
| `badge-text`      | 12px   | 500    | 1.33        | 0              | Badge / pill labels                                |
| `caption-sm`      | 12px   | 400    | 1.33        | 0              | Fine print                                         |

### Principles

- Section titles at 20px weight 500 — never heavier.
- The only uppercase + tracked text is: **button labels** (14px, 600, 0.7px), **table headers** (12px, 600, 0.7px), **stat card titles** (12px, 600, 0.7px), and **info labels** (12px, 600, 0.7px).
- Helptext and empty states use `--graphite` (#636363).
- Table header text uses uppercase with 0.7px tracking, styled differently from body.

## Layout & Spacing

### Spacing System

- **Base unit**: 8px. Scale: `xxs` 4px · `xs` 8px · `sm` 12px · `md` 16px · `lg` 20px · `xl` 24px · `xxl` 32px · `section` 80px
- **Card internal padding**: defaults to `xl` (24px) via `wp-desa-card-pad` or inline `var(--sp-xl)`
- **Card bottom margin**: `xxl` (32px)
- **Form grid padding**: 20px per `wp-desa-form-grid`
- **Filter bar padding**: `sm` 12px vertical, `xl` 24px horizontal
- **Table cell padding**: `sm` 12px vertical, `xl` 24px horizontal (th: 14px vertical)

### Container Structure (AdminLayout)

The page layout follows a three-layer structure:

1. **`wp-desa__globalnav`** — top navigation bar:
   - White (`--canvas`), 64px height, sticky top at 32px (below WP admin bar)
   - Bottom border: 1px solid `--fog`
   - Contains: brand logo left, nav link list right
   - Nav links: 16px body, `--ink` color, active state gets `--primary` underline

2. **`wp-desa__subnav`** — secondary tab navigation:
   - White, below globalnav, bottom border 1px `--fog`
   - Title (uppercase, 14px, 600, `--graphite`) + pill-shaped tab list
   - Tab pills: default white, active `--ink` with `--on-ink` text, `--rounded-pill` radius
   - Tabs link to `?page=X&tab=Y`

3. **`wp-desa__content`** — main content area:
   - Top padding: `xxl` (32px)
   - Contains the page template output

### Page Sections

- **Page header** (optional): `wp-desa-header` with `wp-desa-title` + `wp-desa-actions`
- **Hero band** (dashboard only): `wp-desa-hero` — dark ink slab (`--ink` background, `--on-ink` text), rounded `xl`, 48px padding, metric row below
- **Cards**: `wp-desa-card` — white, `--rounded-xl` (16px), 1px `--fog` border, `overflow: hidden`
- **Filter bar**: `wp-desa-filter-bar` — flex row with padding, border-bottom inside a card
- **Form actions**: `wp-desa-form-actions` — `--cloud` background, border-top, right-aligned buttons

## Elevation & Depth

| Level         | Treatment                           | Use                                     |
| ------------- | ----------------------------------- | --------------------------------------- |
| 0 — Flat      | No border, no shadow                | Section bands, page backgrounds         |
| 1 — Hairline  | 1px solid `--fog` border, no shadow | Cards, table cells, inputs, filter bars |
| 2 — Soft Lift | `0 2px 8px rgba(26,26,26,0.08)`     | Stat cards (`wp-desa-stat-card`)        |
| 3 — Modal     | `0 8px 24px rgba(26,26,26,0.12)`    | Modal overlay content                   |

The system is mostly flat — depth comes from **hairline borders** (`--fog`) on white cards, not shadows. Only stat cards get a soft lift shadow. Modals use the modal shadow.

## Shapes & Border Radius

### Two-tier Radius Philosophy

- **Interactive elements** (buttons, inputs, selects, textareas): `--rounded-md` = **4px** — sharp, rectilinear
- **Containers** (cards, modals, stat cards, hero): `--rounded-xl` = **16px** — soft, enveloping
- **Pills** (badges, tabs, subnav tabs): `--rounded-pill` = **9999px**
- **Icons/stat icons**: `--rounded-lg` = **8px**

| Token            | Value  | Use                                                                    |
| ---------------- | ------ | ---------------------------------------------------------------------- |
| `--rounded-none` | 0px    | Hero band, full-width bands                                            |
| `--rounded-xs`   | 2px    | Minor decorative elements                                              |
| `--rounded-sm`   | 3px    | (not commonly used)                                                    |
| `--rounded-md`   | 4px    | Buttons, inputs, selects, textareas, form actions, pagination controls |
| `--rounded-lg`   | 8px    | Stat icon containers, cards with softer corners                        |
| `--rounded-xl`   | 16px   | Cards, modals, stat cards, hero, image previews                        |
| `--rounded-pill` | 9999px | Badges, subnav tabs, status pills, toggle slider                       |

## Components

### Buttons

Base class `wp-desa-btn` — inline-flex centered, 44px height, uppercase label (14px, 600, 0.7px tracking), `--rounded-md` (4px), 1px border, gap `xs` between icon and text.

**`wp-desa-btn-primary`** — the lone blue CTA

- Background `--primary` (`#024ad8`), text `--on-ink`, border `--primary`
- Hover: `--primary-deep` (`#0e3191`)
- Used for: "Simpan", "Tambah Data", primary form submit

**`wp-desa-btn-secondary`** — white outlined CTA

- Background `--canvas`, text `--ink`, border `--fog`
- Hover: `--cloud` background, `--steel` border
- Used for: "Batal", "Edit", secondary actions

**`wp-desa-btn-danger-outline`** — danger outlined CTA

- Background `--canvas`, text `--bloom-deep`, border `--bloom-rose`
- Hover: `--bloom-rose` background
- Used for: "Hapus" actions in table rows

**`wp-desa-btn-sm`** — compact variant

- Padding 6px 14px, 12.6px font, 700 weight, 0.126px tracking
- Used for: inline action buttons in table rows (Edit, Hapus)

**`wp-desa-btn-danger`** — filled danger CTA

- Background `--bloom-rose`, text `--bloom-deep`, border `--bloom-rose`
- Used sparingly for destructive confirmations

Icons inside buttons use inline Lucide SVGs at 16-18px, with `vertical-align:middle` or flex alignment.

### Cards & Containers

**`wp-desa-card`** — the universal content container

- Background `--canvas`, `--rounded-xl` (16px), 1px `--fog` border, `overflow: hidden`, margin-bottom `--xxl` (32px)
- Internal content uses its own padding via child elements
- Inner variants:
  - `wp-desa-filter-bar`: flex row, padding `sm`/`xl`, border-bottom, flex-wrap
  - `wp-desa-form-grid`: block layout with 20px padding, `xl` gap between children
  - `wp-desa-form-actions`: footer bar, `--cloud` background, border-top, right-aligned
  - Pagination: `wp-desa-pagination` — flex row, `--cloud` background, border-top, padding `sm`/`xl`

**`wp-desa-card-pad`** — card with internal padding

- Adds `--sp-xl` (24px) padding to `wp-desa-card`

**`wp-desa-stat-card`** — dashboard stat card

- Same as card + `--shadow-soft-lift` shadow
- Contains: `wp-desa-stat-title` (uppercase, 12px, 600, 0.7px), `wp-desa-stat-value` (32px, 500), `wp-desa-stat-desc` (14px)

**`wp-desa-hero`** — dark band for dashboard

- Background `--ink`, text `--on-ink`, `--rounded-xl`, padding 48px `--xxl`
- Contains `wp-desa-hero__head` (eyebrow + title + sub) and `wp-desa-hero__metrics` (metric row)

### Tables

**`wp-desa-table`** — data table

- Full width, collapsed borders, left-aligned
- `thead th`: uppercase 12px 600, 0.7px tracking, `--graphite` color, 14px vertical padding `--xl` horizontal, bottom border `--fog`
- `tbody td`: 14px, `--ink` color, `sm` vertical padding `--xl` horizontal, bottom border `--fog`
- `tr:last-child td`: no bottom border
- `tbody tr:hover td`: `--cloud` background highlight
- Inside `wp-desa-card` with overflow-x:auto wrapper for scroll

**`wp-list-table`** (WordPress native) — used in perangkat and peta pages

- Same visual style, uses WordPress's widefat class with wp-desa card wrapper

**Empty state** (`wp-desa-empty-state`):

- Centered text, `--graphite` color, padding `--section` (80px), icon + message

### Form Elements

**`wp-desa-input`**, **`wp-desa-select`**, **`wp-desa-textarea`** — form controls

- Width 100%, padding `sm`/`md`, `--rounded-md` (4px), 1px `--steel` border
- Font: 16px 400, `--ink` color, `--font-body`
- Height: 44px (inputs and selects)
- Focus: no outline, border color changes to `--ink`
- Placeholder: `--graphite`

**Select dropdown**: custom chevron arrow via SVG background-image (pointing down)

**`wp-desa-label`** — form label

- Block, 14px 500, `--ink`, margin-bottom `--xs` (8px)

**Required indicator**: `wp-desa-req` — red asterisk, color `--error`

### Badges / Pills

**`wp-desa-badge`** — status pill

- Inline-block, padding 4px 12px, `--rounded-pill`, 12px 500
- `wp-desa-badge-default`/`wp-desa-badge-pending`: `--cloud` bg, `--ink` text
- `wp-desa-badge-success`/`completed`/`resolved`: `#e6f4ea` bg, `--success` text
- `wp-desa-badge-warning`/`in_progress`: `#fef3e4` bg, `--warning` text
- `wp-desa-badge-danger`/`rejected`: `#fce8e6` bg, `--error` text

### Modal

**`wp-desa-modal-overlay`** — fixed full-screen backdrop

- `rgba(26, 26, 26, 0.55)`, z-index 10000, flex center, hidden by default
- `.is-open` class toggles `visibility: visible; opacity: 1; transition 0.2s`

**`wp-desa-modal-content`**: white, `--rounded-xl`, `--shadow-modal`, max-width 600px, max-height 90vh, slide-in animation

**`wp-desa-modal-header`**: flex row, padding `xl`/`xxl`, border-bottom `--fog`
**`wp-desa-modal-title`**: 20px 500, `--font-display`
**`wp-desa-modal-body`**: padding `--xxl` (32px)
**`wp-desa-modal-footer`**: `--cloud` bg, border-top, flex-end with gap

### Navigation

**Top nav** (`wp-desa__globalnav`): sticky at top:32px, white, 64px, bottom border, flex layout

- Brand (`wp-desa__brand`): icon + text, weight 500, 16px
- Nav links (`wp-desa__navlink`): 16px, padding `xs`/`md`, inline height, 2px transparent bottom border
- Active nav link: `--primary` color + `--primary` bottom border, weight 500

**Subnav** (`wp-desa__subnav`): below globalnav, white, bottom border 1px `--fog`

- Title: uppercase 14px 600, `--graphite`
- Tabs (`wp-desa__subnav-tab`): pill-shaped, 14px 500, padding 6px 14px
- Active tab: `--ink` bg, `--on-ink` text

### Icons

All icons should be **inline Lucide SVG** elements at 16-18px:

- `width="16" height="16"` for table action buttons
- `width="18" height="18"` for primary CTA buttons
- `vertical-align: middle` for inline button icons

Common icon mappings:

- Tambah/Add: `layers-plus`
- Edit: `clipboard-pen`
- Hapus/Delete: `trash-2`
- Simpan/Save: `file-plus` or checkmark
- Kembali/Back: `arrow-left` (WordPress dashicons `dashicons-arrow-left-alt2` is accepted)

### Inline Actions

**`wp-desa-inline-actions-end`**: flex row, `justify-content: flex-end`, gap `xs`, used in table cells for action button groups.
**`wp-desa-inline-actions`**: flex row, gap 6px.

## Page View Patterns

### List+Form Pattern (residents, perangkat, keuangan)

Pages follow a two-view pattern using `?action=URL` parameter:

1. **List view** (`action=list`, default):
   - `wp-desa-card` containing:
     - `wp-desa-filter-bar` with total count + "Tambah Data" primary CTA
     - `wp-desa-table` with data rows
     - `wp-desa-pagination` (if multi-page)
   - Each row has inline action buttons (Edit + Hapus)

2. **Form view** (`action=add` or `action=edit&id=X`):
   - "Kembali" link at top (arrow + text)
   - `wp-desa-card` with:
     - Section title (h3)
     - `<form method="post">` with:
       - hidden nonce, save flag, and id fields
       - `wp-desa-form-grid` containing `wp-desa-form-group` items
       - `wp-desa-form-actions` with "Batal" (secondary) + "Simpan" (primary)

### Tab Pattern (settings, pemerintahan, dokumentasi, finances)

Subnav tabs via `wp-desa__subnav-tabs` with `?page=X&tab=Y` URL routing.
Each tab renders a different template or section of the page.

## Responsive

### Breakpoints

| Width   | Key Changes                                                                                               |
| ------- | --------------------------------------------------------------------------------------------------------- |
| < 782px | Globalnav stacks vertically, form grid collapses to 1 column, stat grid to 1 column, hero padding reduces |
| Desktop | Default layout with side-by-side form grids (2 columns)                                                   |

### Touch Targets

Buttons at 44px height meet touch target requirements. Action buttons in table rows use `wp-desa-btn-sm` which collapses below 44px — acceptable for mouse users, use sparingly.

## Do's and Don'ts

### Do

- Reserve `#024ad8` (`--primary`) for the primary CTA — at most one per view
- Wrap data tables in `wp-desa-card` with `overflow-x:auto` for scroll safety
- Use `wp-desa-btn-sm` + Lucide SVG icons for table row actions (Edit, Hapus)
- Set button labels in uppercase with 0.7px tracking — the only tracked text besides table headers
- Use `--fog` hairline borders to separate card sections (filter-bar, pagination, form-actions)
- Use `--cloud` background for footer bars (pagination, form actions, modal footer)
- Keep form inputs at 44px height with 4px border radius
- Use `wp-desa-label` at 14px 500 for form fields
- Use inline Lucide SVGs for all icons, sized 16-18px

### Don't

- Don't add secondary saturated colors outside the blue family + semantic (red/green/amber) palette
- Don't use heavy shadows — depth is via hairline borders and `--shadow-soft-lift` only on stat cards
- Don't round buttons above 4px; a soft 8px+ button breaks the two-tier radius system
- Don't use lowercase for button labels — uppercase + tracking is the system convention
- Don't put interactive actions outside `wp-desa-filter-bar` or `wp-desa-inline-actions-end`
- Don't use modal popups for add/edit forms — use the List+Form URL-driven pattern instead
- Don't use `display:grid` on `wp-desa-form-grid` until the form has multi-column layout; otherwise use block with vertical spacing

## Iteration Guide

1. Match the existing component vocabulary — don't introduce new class names when `wp-desa-card`, `wp-desa-btn`, `wp-desa-table`, `wp-desa-input` etc. already cover the use case
2. Use the List+Form URL-driven pattern (not modals) for CRUD pages — refer to residents.php or perangkat.php as the template
3. Keep `#024ad8` usage minimal — one primary CTA per view, no decorative blue
4. Wrap card content sections (filter-bar, table, pagination, form-actions) as direct children of `wp-desa-card` using border separators
5. Always include nonce fields and hidden ID inputs in forms following the `wp_desa_save_*` / `check_admin_referer` pattern
6. Redirect after POST to the list view URL to avoid re-submission
7. For Lucide icons, inline the full SVG element; never reference external icon libraries
