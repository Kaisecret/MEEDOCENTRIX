# Styling for Laravel Blade + Bootstrap — MEEDOCentrix

Use this styling guidance for the real MEEDOCentrix frontend stack.

## Core styling rule

Prefer styling in this order:

1. Bootstrap layout, spacing, and component classes
2. Existing project CSS in `public/css/styles.css`
3. Small page-specific styles in Blade only when the change is truly local

Do not write Tailwind- or React-specific styling guidance for this repository.

---

## App Color System (from `public/css/styles.css`)

The project uses a **Premium Blue Enterprise Theme**. These are the actual CSS custom properties defined in `:root`:

### Primary Blues (main brand)

| Token              | Hex       | Usage                              |
|--------------------|-----------|--------------------------------------|
| `--primary-900`    | `#0a1628` | Deepest navy, sidebar dark areas     |
| `--primary-800`    | `#0f2240` | Very dark headings                   |
| `--primary-700`    | `#152e56` | Deep panel backgrounds               |
| `--primary-600`    | `#1a3a6c` | Secondary dark blue                  |
| `--primary-500`    | `#1e4d8c` | Rich mid-blue, stat card top borders |
| `--primary-400`    | `#2563eb` | **Main interactive blue** — links, focus rings, icons, buttons |
| `--primary-300`    | `#3b82f6` | Hover highlights, active indicators  |
| `--primary-200`    | `#60a5fa` | Light blue accents                   |
| `--primary-100`    | `#93bbfd` | Very light blue tints                |
| `--primary-50`     | `#dbeafe` | Lightest blue backgrounds            |

### Neutrals (text, backgrounds, borders)

| Token          | Hex       | Usage                          |
|----------------|-----------|----------------------------------|
| `--gray-900`   | `#111827` | Strongest headings               |
| `--gray-800`   | `#1f2937` | Body text, primary text          |
| `--gray-700`   | `#374151` | Labels, secondary headings       |
| `--gray-600`   | `#4b5563` | Muted text                       |
| `--gray-500`   | `#6b7280` | Helper text, captions            |
| `--gray-400`   | `#9ca3af` | Placeholders, disabled           |
| `--gray-300`   | `#d1d5db` | Borders, dividers                |
| `--gray-200`   | `#e5e7eb` | Light borders, input borders     |
| `--gray-100`   | `#f3f4f6` | Page backgrounds                 |
| `--gray-50`    | `#f9fafb` | Subtle card backgrounds          |
| `--white`      | `#ffffff` | Cards, panels, inputs            |

### Accent / Status Colors

| Token              | Hex       | Usage                          |
|--------------------|-----------|----------------------------------|
| `--success`        | `#10b981` | Success states, paid badges      |
| `--success-light`  | `#d1fae5` | Success badge backgrounds        |
| `--warning`        | `#f59e0b` | Warning states, pending badges   |
| `--warning-light`  | `#fef3c7` | Warning badge backgrounds        |
| `--danger`         | `#ef4444` | Danger states, delete actions    |
| `--danger-light`   | `#fee2e2` | Danger backgrounds               |
| `--info`           | `#06b6d4` | Info states                      |
| `--info-light`     | `#cffafe` | Info backgrounds                 |
| `--purple`         | `#8b5cf6` | Special accents                  |
| `--purple-light`   | `#ede9fe` | Special accent backgrounds       |

### Fishport Page-Specific Palette

The fishport transaction pages use a scoped set of variables on `.fishport-page`:

| Variable           | Hex       | Maps to                         |
|--------------------|-----------|----------------------------------|
| `--fp-bg`          | `#f3f7fb` | Page background                  |
| `--fp-panel`       | `#ffffff` | Card/panel backgrounds           |
| `--fp-line`        | `#d9e3ef` | Borders, dividers                |
| `--fp-text`        | `#0f2740` | Primary text                     |
| `--fp-muted`       | `#5e7188` | Secondary/helper text            |
| `--fp-primary`     | `#155f8f` | **Primary blue for fishport**    |
| `--fp-primary-dark`| `#0f4b72` | Darker blue for hover states     |
| `--fp-soft`        | `#e9f2fa` | Subtle blue tinted backgrounds   |
| `--fp-danger`      | `#c0392b` | Danger/delete color              |
| `--fp-danger-soft` | `#fceceb` | Danger background tint           |
| `--fp-success`     | `#1f8f67` | Success/paid color               |
| `--fp-success-soft`| `#e7f7f1` | Success background tint          |
| `--fp-warning`     | `#9a6a00` | Warning/not-paid color           |
| `--fp-warning-soft`| `#fff4dd` | Warning background tint          |

---

## Design Tokens

### Shadows

| Token          | Value                                                                 |
|----------------|----------------------------------------------------------------------|
| `--shadow-sm`  | `0 1px 2px rgba(0,0,0,0.05)`                                         |
| `--shadow-md`  | `0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05)` |
| `--shadow-lg`  | `0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05)`|
| `--shadow-xl`  | `0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05)`|

### Border Radius

| Token          | Value  |
|----------------|--------|
| `--radius-sm`  | `6px`  |
| `--radius-md`  | `10px` |
| `--radius-lg`  | `14px` |
| `--radius-xl`  | `20px` |

### Transitions

| Token               | Value           |
|----------------------|-----------------|
| `--transition`       | `all 0.2s ease` |
| `--transition-slow`  | `all 0.3s ease` |

---

## Typography

- **Font Family:** `'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`
- **Body text:** `var(--gray-800)` / `0.9rem`
- **Page titles:** `800` weight, `var(--gray-900)` or `#0f172a`
- **Section titles:** `700` weight, `1.1rem–1.2rem`
- **Helper text:** `var(--gray-500)` or `var(--fp-muted)`
- **Labels:** `600` weight, `0.8rem`, `var(--gray-700)`
- **Table headers:** `700` weight, `0.84rem`, uppercase, letter-spacing `0.03em`

Typography should feel readable, structured, calm, and professional. Avoid decorative typography choices that do not fit an internal office system.

---

## Sidebar Design

- **Background:** `linear-gradient(145deg, #1e3a8a 0%, #2563eb 100%)`
- **Width:** `272px` (collapsible to `0px` on mobile)
- **Text:** White with rgba opacity for secondary text
- **Active nav item:** Highlighted with `rgba(255,255,255,0.12)` bg + left `3px` white border
- **Icons:** Font Awesome, `rgba(255,255,255,0.6)` default, white when active

Do NOT change the sidebar design when redesigning content pages.

---

## Design Direction Rules

These rules must always be followed:

1. **Main color is Blue.** The primary brand color is blue (`--primary-400: #2563eb` globally, `--fp-primary: #155f8f` on fishport pages). Do NOT change or replace the blue palette.
2. **No gradients in content areas.** Use flat, clean solid backgrounds for cards, panels, heroes, and buttons. Gradients are only used on the sidebar and login page.
3. **No dark mode.** The app is light mode only.
4. **shadcn/ui as visual inspiration only.** Use shadcn-style clean spacing, flat cards, subtle borders, and refined geometry — but implement in Blade + Bootstrap, not as a dependency.
5. **Keep existing class names.** UI redesigns should only change CSS properties, never rename or remove HTML classes that JavaScript relies on.

---

## Use Bootstrap first

Prefer built-in Bootstrap structure and utilities:

- `container`, `container-fluid`
- `row`, `col-*`
- `d-flex`, `justify-content-*`, `align-items-*`
- `gap-*`
- `mb-*`, `mt-*`, `py-*`, `px-*`
- `card`, `alert`, `table`, `badge`, `btn`, `modal`

Do not describe styling using Tailwind utility systems, `className`, `cn()`, or component variants from React libraries.

---

## Keep styles consistent with the existing app

When updating or reviewing UI:

- Preserve the existing blue enterprise/admin direction
- Keep spacing clean and calm
- Avoid introducing a completely different design language
- Match the elevation and border patterns from `public/css/styles.css`

---

## Component Patterns

### Cards / Panels

- Background: `var(--white)` or `var(--fp-panel)`
- Border: `1px solid var(--gray-200)` or `var(--fp-line)`
- Radius: `var(--radius-lg)` (14px) or `16px`
- Shadow: `var(--shadow-md)` or a light `box-shadow`
- Headers: Light bg (`#fbfdff` or `var(--gray-50)`), bottom border

### Buttons

- Primary: Blue bg (`--primary-400`), white text, rounded (`10px`), subtle shadow
- Muted: Light gray bg, dark text
- Outline: White bg, blue border
- Danger: White bg, red border, red text
- Hover: Slight `translateY(-1px)` lift + shadow increase

### Inputs

- Border: `1px solid var(--gray-200)` or `#c4d3e2`
- Radius: `10px`
- Focus ring: `0 0 0 3px rgba(37,99,235,0.1)` with blue border
- Background: White, readonly gets `var(--gray-50)`

### Tables

- Sticky header with light blue bg (`#eef5fb`)
- Uppercase header text, letter-spacing `0.03em`
- Zebra striping with `#fafcfe` on even rows
- Row hover highlight `#f0f7ff`

### Status Badges

- **Paid:** `#ecfdf5` bg, `#047857` text, `#a7f3d0` border
- **Not Paid:** `#fffbeb` bg, `#b45309` text, `#fde68a` border
- Pill shape (`border-radius: 999px`), uppercase, small font

### Modals

- Overlay: `rgba(15, 39, 64, 0.45)`
- Card: White bg, `var(--radius-lg)` radius, `var(--shadow-xl)`
- Header: White or tinted bg depending on action (e.g., red for delete)
- Footer: Light gray (`#f8fafc`) bg

---

## Spacing and layout

Prefer:

- `gap-*` for flex/grid spacing
- Bootstrap grid for form and dashboard layout
- Consistent vertical rhythm between headings, filters, cards, and tables

Avoid:

- Random spacing values that do not match nearby sections
- Crowded form rows
- Oversized headers or action areas that overpower the page

---

## Responsive behavior

Every styling decision should be checked for:

- Desktop dashboard readability
- Tablet layout stability
- Mobile form and table usability

The app uses `860px` as the primary mobile breakpoint. Use Bootstrap's responsive classes before inventing custom responsive systems.

---

## Inline styles

Inline styles are allowed in this project when:

- A page has a one-off layout need
- The style is tightly tied to a single Blade file
- Moving it to shared CSS would not improve reuse

But if the same style pattern shows up repeatedly, move it into shared CSS.

---

## Styling checklist

Before finalizing:

1. Is Bootstrap doing most of the work?
2. Does the page visually match the rest of MEEDOCentrix?
3. Are the correct blue theme colors being used from the design system?
4. Should repeated inline styles be moved into shared CSS?
5. Is spacing clean and consistent?
6. Are gradients avoided in content areas?
7. Does the result feel polished, professional, and practical for office use?
