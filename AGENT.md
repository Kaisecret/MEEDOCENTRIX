# AGENTS.md

## Project Overview

Project **MEEDOCentrix** is a centralized **web-based comprehensive data management system** for the Municipal Economic Enterprise Development Office (MEEDO) of San Jose, Antique. It is designed to manage and monitor daily operations involving the **fishport, public market, terminal (TFCO), cemetery, and atrium hall booking** through a secure and organized digital platform.

The system handles **vessel and vehicle logging, product entries, stall rentals, bookings, payment processing, fee computation, report generation, and dashboard monitoring**. It is developed using **Laravel**, **Blade templates only**, **MySQL**, and **XAMPP**, with **Bootstrap** and **shadcn/ui-inspired design patterns** for a clean, modern, and user-friendly interface.

## Project Rules

### Laravel/Blade Rules

- Always follow **Laravel conventions** for routing, controllers, models, migrations, validation, and authentication.
- Use **Blade only** for frontend rendering.
- Do not use **React, Vue, Inertia, Livewire, or Next.js** in this project.
- Use **Bootstrap** as the main UI framework.
- Apply **shadcn/ui-inspired design patterns** only as visual inspiration adapted for Blade.
- Keep the UI clean, modern, well-spaced, and polished like a professionally designed admin system.
- Always check that the **logic is correct**, the code is maintainable, and the UI is both functional and visually refined.
- Prefer reusable Blade layouts, partials, and components for repeated UI patterns.
- Avoid large controllers or overly long Blade files; split into smaller maintainable parts when needed.
- Use **MySQL** for database storage and manage schema changes through Laravel migrations.
- Use **XAMPP** as the local development environment.

### Code Quality Rules

- Always review whether the implementation is logically correct before finalizing.
- Prefer readable, maintainable, and scalable code over quick but messy solutions.
- Keep business logic out of Blade templates as much as possible.
- Use Laravel validation and built-in security features whenever possible.
- Use role-based access and proper authentication for authorized users.
- Make sure the UI looks polished, aligned, and user-friendly, with attention to spacing, hierarchy, and consistency.
- Act like a strong coding assistant with excellent UI judgment and prioritize both **correct functionality** and **designer-quality presentation**.

## Tech Stack

This project uses the following stack:

- **Framework:** Laravel
- **Templating Engine:** Blade only
- **Database:** MySQL
- **Local Server Environment:** XAMPP
- **Frontend:** HTML, CSS, JavaScript
- **UI Framework:** Bootstrap
- **UI Components:** shadcn/ui-inspired design components adapted for Blade
- **Development Tool:** Visual Studio Code

## Development Setup

The system is developed as a **web-based application** using Laravel as the main framework.  
For the user interface, the project uses **Blade templates only** for rendering pages and views.  
The project uses **MySQL** as the database and **XAMPP** as the local development environment to run Apache and MySQL services.  
For the frontend design and layout, the system uses **Bootstrap** to provide responsive structure, styling, and reusable interface components.  
The project also utilizes **shadcn/ui-inspired components and design patterns** as a reference for creating clean, modern, and consistent user interface elements within Blade templates.

## Development Guidelines

- Use **Laravel conventions** for routing, controllers, models, migrations, validation, and authentication.
- Use **Blade templates only** for frontend rendering.
- Do not use React, Vue, Inertia, Livewire, or other frontend rendering frameworks unless the project is changed later.
- Use **Bootstrap** for layout, responsive design, forms, buttons, navigation, modals, tables, and other interface elements.
- Apply **shadcn/ui-inspired styling and component structure** where appropriate, while implementing them in a Blade-based Laravel setup.
- Store and manage application data in **MySQL**.
- Run the project locally through **XAMPP**.
- Organize the code clearly using Laravel's MVC structure.

## Database and Server Notes

- All application records shall be stored in **MySQL**.
- Database tables should be created and managed through Laravel migrations whenever possible.
- XAMPP shall provide the required Apache and MySQL services for local testing and development.
- The system should be tested in a browser through the local XAMPP environment before deployment.

## UI and Design Notes

- **Bootstrap** shall be used as the primary frontend CSS framework for responsive and structured page design.
- **Blade** shall be used to build reusable layouts, partials, and components.
- **Design Philosophy:** Act as a professional UI/UX designer. Keep the design **NICE and PREMIUM**.
- **Color Palette:** The **main color** of the app is **Blue (#155f8f)**. Do **NOT** change this main color.
- **Gradients:** Remove any and all gradients (no design should use gradients). Use flat, clean background colors.
- **shadcn/ui-inspired design** may be used as a guide for flat cards, forms, tables, dialogs, alerts, and dashboard elements, but implementation shall remain compatible with Laravel Blade while adhering to the Blue primary color constraint.
- The interface should remain exceptionally clean, user-friendly, modern, and practical for office use.

## Project Conventions for This Laravel Application

In the Laravel project folder where the application code lives:

- Use **Laravel** as the main application framework and keep the project organized using the MVC structure.
- Use **Blade only** for rendering views and frontend pages.
- Use **Bootstrap** as the main UI framework for responsive layout and interface styling.
- Apply **shadcn/ui-inspired design patterns** only as visual references that are adapted into Blade-compatible components.
- Use **MySQL** as the main database and manage schema changes through Laravel migrations whenever possible.
- Use **XAMPP** as the local development environment for Apache and MySQL services.
- Install any commands, Composer packages, or npm packages the project depends on before running development or build steps.
- Do not introduce React, Vue, Inertia, Livewire, or other frontend rendering frameworks unless the project requirements are officially changed.
- Do not add unnecessary packages or libraries when Laravel’s built-in features can already handle the requirement.
- Keep controllers focused on request handling and business flow; move reusable logic into services, actions, or appropriate classes when needed.
- Keep Blade templates clean and readable by extracting repeated UI into partials or Blade components.
- Prefer clear and descriptive route names, controller names, and variable names.
- Avoid creating helper methods or utility functions that are used only once.
- Avoid very large controllers, Blade files, or service classes. If a file becomes too long, split related logic into smaller modules or components.
- Prefer reusable Blade layouts, includes, and components for common UI patterns such as forms, tables, cards, alerts, modals, and dashboards.
- Use Laravel validation rules for request validation instead of placing validation logic directly in views.
- Use Eloquent relationships and query scopes where appropriate to keep queries clear and maintainable.
- Keep sensitive settings in the `.env` file and never hardcode secrets, credentials, or environment-specific values in source files.
- Use secure authentication and role-based access control for all authorized users of the system.
- Store passwords securely using Laravel’s built-in hashing mechanisms.
- For database-backed features, ensure records are properly validated, stored, retrieved, and updated through Laravel conventions.
- When adding or changing database tables, also update related migrations, models, validation rules, and documentation if applicable.
- When adding file uploads, ensure validation, storage handling, and access rules are clearly defined.
- When generating reports, keep export formats and calculations consistent with office requirements.
- Prefer private or narrowly scoped methods where possible and expose only what is needed.
- Keep code readable, practical, and easy for future maintenance.

## Suggested Laravel Workflow

After making changes in the project, follow this workflow when applicable:

1. Run Laravel formatting or code style tools used by the project, such as **Laravel Pint**.
2. Run the relevant feature or unit tests for the part of the project that was changed.
3. If database structure changes were made, review and test the related migrations carefully.
4. If UI changes were made, review the affected Blade pages in the browser and verify responsiveness with Bootstrap layouts.
5. If major shared logic was changed, run the broader test suite before finalizing.
6. Do not skip updating documentation or notes when a major feature, module, or process changes.

## Summary

The project is built using **Laravel**, with **Blade** as the only templating engine, **MySQL** as the database, and **XAMPP** as the local server environment. It also uses **Bootstrap** for responsive frontend design and **shadcn/ui-inspired interface patterns** to support a modern and consistent user experience. This setup supports a structured, maintainable, and practical web-based system for development and deployment.
