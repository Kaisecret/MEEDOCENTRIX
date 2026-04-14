# Blade / Bootstrap Implementation Rules

This project is **not** a React, Radix, or shadcn component codebase. It is a **Laravel + Blade + Bootstrap** system.

Use this file to translate modern UI ideas into the actual stack used by MEEDOCentrix.

## Core rule

When building or reviewing UI in this repository:

- use **Blade views** in `resources/views`
- use **Laravel routes/controllers/models/migrations**
- use **Bootstrap classes and plain HTML**
- use **project CSS** from `public/css/styles.css` when Bootstrap utilities are not enough
- use **small Blade includes/components** when reuse is needed

Do **not** write guidance based on:

- `TabsTrigger`
- `DialogTitle`
- `PopoverTrigger`
- `ToggleGroup`
- `InputGroupAddon`
- `cn()`
- `lucide-react`
- `sonner`
- `radix`
- React state/component APIs

---

## Actual project structure

Use the current Laravel structure in this repo:

- `app/Http/Controllers`
- `app/Models`
- `app/Http/Middleware`
- `routes/web.php`
- `database/migrations`
- `database/seeders`
- `resources/views/layouts`
- `resources/views/admin`
- `resources/views/fishport`
- `resources/views/market`
- `resources/views/cemetery`
- `resources/views/terminal`
- `resources/views/atrium`
- `resources/views/collector`
- `resources/views/cashier`
- `resources/views/shared`

If a screen belongs to a department, place it in that department folder and route it through Laravel.

---

## Page composition: Blade instead of component frameworks

**Incorrect mindset:**

- build a page by nesting framework UI components
- rely on client-side component composition
- assume reusable UI means React props and JSX composition

**Correct approach in this project:**

- extend a Blade layout such as `resources/views/layouts/app.blade.php`
- keep page content in `@section('content')`
- extract repeated sections into Blade partials or Blade components when reuse is real
- keep routing and access flow in Laravel routes, controllers, and middleware

Example pattern:

```blade
@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h1 class="h5 mb-1">Page Title</h1>
                <p class="text-muted mb-0">Helpful subtitle</p>
            </div>
            <div class="card-body">
                ...
            </div>
        </div>
    </div>
@endsection
```

---

## Navigation and routing: Laravel instead of client routing

Use:

- `route('name')`
- `url('/path')`
- `redirect()->route(...)`
- middleware in `app/Http/Middleware`
- route groups in `routes/web.php`

Do not design flows around client-only navigation helpers.

---

## Dialogs, dropdowns, overlays

Use the tools that fit this stack:

- Bootstrap modal markup for modal dialogs
- Bootstrap dropdowns when a dropdown is needed
- plain Blade sections or shared partials for reusable modal content
- server-rendered confirmation pages or form posts for sensitive actions

Do not write guidance that depends on Radix dialog, popover, drawer, sheet, or hover-card APIs.

---

## Tabs, filters, and switching views

In this project, tabs should usually be implemented using one of these:

1. Bootstrap nav tabs for simple visual switching
2. Separate routes for clearer department pages
3. Small vanilla JavaScript for lightweight UI toggling inside a Blade page

Prefer route-based separation when the content is large or logically distinct.

---

## Forms and state

Use Laravel form submission and validation flow:

- HTML forms
- `@csrf`
- `@method('PUT')`, `@method('DELETE')` when needed
- `old(...)`
- `$errors`
- controller or form request validation

Do not assume React-managed form state or component props for validation/UI state.

---

## Icons

This project currently uses **Font Awesome** in Blade layouts and pages.

Prefer:

- Font Awesome classes already used by the project
- inline SVG only when there is a strong reason

Do not instruct the agent to import `lucide-react`, `tabler`, or other React icon libraries here.

---

## Styling

Prefer styling in this order:

1. Bootstrap layout and utility classes
2. Existing project CSS in `public/css/styles.css`
3. Small local page styles inside Blade only when necessary

Do not write Tailwind-specific or React component-variant guidance for this repository.

---

## Summary

When translating polished admin UI ideas into this repo:

- think in **Blade templates**
- think in **Bootstrap layout**
- think in **Laravel routes/controllers/middleware**
- think in **department view folders**
- think in **server-rendered forms and validation**

That is the correct equivalent of component-framework guidance for MEEDOCentrix.
