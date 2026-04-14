# Forms & Inputs for Laravel Blade

These are the form rules for MEEDOCentrix.

Use Laravel + Blade + Bootstrap patterns, not React component rules.

## Core rule

Every form should be:

- server-submitted through Laravel routes
- protected with `@csrf`
- validated in controllers or form requests
- repopulated with `old(...)` where appropriate
- connected to visible validation feedback

---

## Standard form structure

Prefer this pattern:

```blade
<form action="{{ route('admin.users.store') }}" method="POST">
    @csrf

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Full Name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
```

---

## Use Bootstrap form controls first

Prefer:

- `form-control` for text inputs and textareas
- `form-select` for selects
- `form-check` for checkboxes and radios
- `input-group` when an icon/button/prefix/suffix is attached
- `row g-*` and `col-*` for form layout

Do not write rules around React-only field wrappers such as `Field`, `FieldGroup`, `ToggleGroup`, or `InputGroupAddon`.

---

## Input groups

If an input needs a leading icon, text prefix, or button:

- use Bootstrap `input-group`
- keep the markup accessible and simple

Example:

```blade
<div class="input-group">
    <span class="input-group-text">
        <i class="fas fa-search"></i>
    </span>
    <input type="text" class="form-control" name="search" placeholder="Search...">
</div>
```

---

## Validation feedback

Validation must not rely only on frontend behavior.

Always prefer Laravel validation and show errors in Blade with:

- `@error('field')`
- `$errors->any()`
- session status/error messages

For invalid inputs:

- add Bootstrap `is-invalid`
- show `invalid-feedback`

For top-of-form errors:

- show a clear alert block above the form

---

## Repopulating values

After validation fails:

- text inputs should use `old('field')`
- selects should compare `old(...)`
- checkboxes/radios should restore checked state

This is especially important for admin, booking, payments, and record-entry forms.

---

## Related field groups

When inputs belong together:

- use a Bootstrap card section
- or use `fieldset` and `legend` for semantic grouping

Examples:

- account information
- department assignment
- payment details
- booking schedule
- report filters

---

## Dates, numbers, and business rules

For operational data:

- validate dates carefully
- validate numeric values with realistic ranges
- validate relationships between fields
- validate uniqueness where duplicate records should be blocked

Examples:

- booking end date should not precede start date
- payment amount should be numeric and non-negative
- user email and username should be unique
- department should match allowed values

---

## Checkboxes and status fields

Use:

- `form-check` for checkboxes/radios
- clear labels beside the control
- hidden assumptions only when well-documented in validation logic

Status toggles such as active/inactive should be easy to understand from both the label and surrounding copy.

---

## Form actions

At the bottom of a form:

- keep primary action obvious
- keep secondary/cancel action available when needed
- align actions consistently

Prefer a small action area with:

- primary submit button
- optional cancel/back button

---

## File uploads

When file uploads are added:

- use `enctype="multipart/form-data"`
- validate file type and file size in Laravel
- define storage handling clearly
- do not trust client-side checks alone

---

## Forms checklist

Before finalizing a form, check:

1. Does it post to the correct named route?
2. Does it use `@csrf` and method spoofing when needed?
3. Are values repopulated with `old(...)`?
4. Are field errors shown properly?
5. Are layout, spacing, and labels clear and professional?
6. Are server-side validation rules strong enough for the data being stored?
