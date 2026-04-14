# Icons for Blade UI

This project uses **Blade-rendered HTML** and currently relies on **Font Awesome** in its layouts and pages.

Do not write icon guidance using React icon imports.

## Preferred icon approach

Use:

- Font Awesome classes already used in the project
- inline SVG only when there is a strong reason and it improves quality

Avoid:

- `lucide-react`
- `@tabler/icons-react`
- JSX icon props
- icon component imports for frontend frameworks not used in this repo

---

## Buttons with icons

Keep button icons simple and readable:

```blade
<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
    <i class="fas fa-save"></i>
    <span>Save Changes</span>
</button>
```

Rules:

- use one icon per button unless there is a strong reason
- keep icon position consistent
- use Bootstrap flex utilities like `d-inline-flex`, `align-items-center`, and `gap-2`
- do not oversize icons inside buttons

---

## Icons in headings, cards, and alerts

Icons may be used to reinforce meaning in:

- stat cards
- section headers
- alerts
- table toolbars
- navigation links

Keep them:

- visually balanced
- secondary to the text
- consistent across similar UI patterns

---

## Navigation icons

In sidebar/top navigation:

- use consistent icon style and size
- keep icon spacing aligned with labels
- avoid mixing unrelated icon styles in the same menu

For this project, the existing Font Awesome navigation pattern should remain the default.

---

## Status icons

Use icons only when they improve clarity:

- success
- warning
- error
- activity state

Do not use icons as the only status indicator. Pair them with text or color.

---

## Decorative vs meaningful icons

Ask:

- does the icon make the action easier to recognize?
- is the meaning already clear without extra decoration?

If the icon does not improve understanding, leave it out.

---

## Icon checklist

Before finalizing:

1. Is the icon source compatible with Blade and the current project?
2. Is Font Awesome enough for this case?
3. Is the icon spacing aligned properly?
4. Does the icon support the label instead of competing with it?
5. Is the same icon treatment used consistently across similar elements?
