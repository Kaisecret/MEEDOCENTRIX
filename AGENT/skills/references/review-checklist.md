# Laravel UI Review Checklist

Use this quick checklist while reviewing or generating code.

## Logic
- Does the flow match the intended business process?
- Are create, read, update, delete, totals, status changes, and filters correct?
- Are edge cases handled?
- Is validation complete and realistic?

## Laravel
- Is the responsibility split cleanly across routes, controller, model, validation, and view?
- Are Eloquent relationships and scopes used appropriately?
- Are secrets and environment values kept out of source files?
- Are authentication and authorization concerns handled properly?

## Database normalization
- Does each table have one clear purpose?
- Is the schema in **3NF** or close to it?
- Are repeated text values better stored in related tables?
- Are there partial or transitive dependencies that should be separated?
- Are foreign keys and relationships used instead of duplicating data?

## Blade/UI
- Are the page title, subtitle, and actions clear?
- Is the layout readable and well-spaced?
- Are cards, forms, tables, and badges consistent?
- Does the page feel polished rather than default-generated?

## UX
- Can the user understand the next action immediately?
- Are errors, empty states, and status indicators clear?
- Is the screen easy to scan?
- Does the interface feel calm, modern, and professional?
