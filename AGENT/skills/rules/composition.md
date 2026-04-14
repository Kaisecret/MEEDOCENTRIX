# Blade Composition

Use this file for structuring pages and reusable UI in the actual MEEDOCentrix stack.

## Core idea

Compose the interface with:

- Blade layouts
- Blade sections
- partials/includes
- optional Blade components
- Bootstrap cards, rows, tables, alerts, modals, and navs

Do not write composition rules using React-only UI components.

---

## Start from layouts

For authenticated pages, prefer extending:

- `resources/views/layouts/app.blade.php`

For guest/auth pages, prefer extending:

- `resources/views/layouts/guest.blade.php`

Keep page-specific content inside the appropriate section instead of duplicating full document structure.

---

## Group pages by feature area

Match the current folder structure:

- admin pages -> `resources/views/admin`
- fishport pages -> `resources/views/fishport`
- market pages -> `resources/views/market`
- cemetery pages -> `resources/views/cemetery`
- terminal pages -> `resources/views/terminal`
- atrium pages -> `resources/views/atrium`
- collector pages -> `resources/views/collector`
- cashier pages -> `resources/views/cashier`
- shared pages -> `resources/views/shared`

If a page belongs to a department, keep it in that department folder instead of mixing unrelated screens together.

---

## Reuse repeated UI with Blade partials

Extract repeated sections when they are truly shared:

- flash alerts
- table toolbars
- dashboard stat cards
- form field groups
- modal bodies
- shared action buttons

Good Blade options:

- `@include(...)`
- `@includeWhen(...)`
- `@each(...)`
- Blade components if the pattern is reused across multiple screens

Do not create a partial for something used only once.

---

## Use Bootstrap structure consistently

Prefer recognizable admin structures:

- page header with title and subtitle
- card-based sections
- clear action row
- filter/search area above tables
- readable table headers
- alert banners for status/error feedback
- empty states when no data exists

Example structure:

```blade
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Users</h1>
            <p class="text-muted mb-0">Manage personnel accounts and access.</p>
        </div>
        <div>
            <a href="{{ route('admin.users') }}" class="btn btn-primary">Refresh</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            ...
        </div>
    </div>
</div>
```

---

## Alerts and feedback

Use Laravel session and validation feedback:

- `session('status')`
- `session('error')`
- `$errors`

Render them using Bootstrap-friendly alert blocks or a shared partial. Keep the styles consistent across pages.

---

## Empty states

When a table or list has no records:

- explain clearly that no records exist
- tell the user what they can do next
- keep the empty state inside the same card/table section when appropriate

Do not leave blank containers with no explanation.

---

## Tables

For records, transactions, and reports:

- put filters/search above the table
- keep headers short and readable
- align actions consistently
- show statuses clearly
- keep dense data scannable

If a table becomes too custom or repeated in many places, extract shared markup into a partial.

---

## Modals

If a page needs modal interactions:

- use Bootstrap modal markup
- keep the modal content organized and labeled
- submit through Laravel forms when data is being changed

Do not document modal composition using React dialog APIs in this project.

---

## Scripts and styles

Prefer:

- shared CSS in `public/css/styles.css`
- shared JS in `public/js/app.js`
- page-local scripts/styles only when the behavior is specific to one page

Keep large inline scripts from taking over Blade pages unless there is a practical reason.

---

## Composition checklist

Before finalizing a screen, check:

1. Does it extend the correct layout?
2. Is the page stored in the correct department/shared folder?
3. Is repeated UI extracted only where reuse is real?
4. Is the structure clear with Bootstrap cards, headings, actions, and feedback?
5. Does the page feel consistent with the rest of the system?
