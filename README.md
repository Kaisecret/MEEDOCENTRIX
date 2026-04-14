# MEEDOCentrix

MEEDOCentrix is a **Web-Based Comprehensive Data Management System for MEEDO** designed for the **Municipal Economic Enterprise Development Office (MEEDO)** of San Jose, Antique. The system centralizes and improves the management of daily operations involving the **fishport, public market, terminal (TFCO), cemetery, and atrium hall booking** through a secure, organized, and user-friendly web platform.

The project is built to replace manual and Microsoft Access-based processes with a more efficient digital system that supports **transaction recording, payment processing, automated fee computation, report generation, dashboard monitoring, and centralized record management**.

---

## Project Purpose

The system is developed to help MEEDO manage its daily services and transactions more accurately and efficiently. It reduces delays, minimizes errors, improves record retrieval, strengthens revenue tracking, and supports better decision-making through centralized data and generated reports.

---

## Key Features

- Vessel and vehicle arrival and departure logging
- Product entry and quantity management
- Stall rental and booking management
- Atrium hall reservation handling
- Terminal, cemetery, and fishport transaction recording
- Automated fee computation
- Payment processing
- Official receipt generation
- Centralized database management
- Dashboard monitoring
- Collection, revenue, occupancy, and transaction reports
- Duplicate entry detection
- Organized and secure record storage

---

## Target Users

The system is intended for authorized MEEDO personnel, including:

- Administrator
- Market Supervisors
- Main Cashier
- Cemetery Staff
- Assigned Collectors
- Terminal Personnel
- Fishport Personnel
- Hall Personnel

Each user has specific responsibilities and access permissions based on their role in the system.

---

## Tech Stack

- **Framework:** Laravel
- **Templating Engine:** Blade only
- **Database:** MySQL
- **Local Server Environment:** XAMPP
- **Frontend:** HTML, CSS, JavaScript
- **UI Framework:** Bootstrap
- **UI Style Direction:** shadcn/ui-inspired design adapted for Blade
- **Development Tool:** Visual Studio Code

---

## UI and Design Direction

The project uses **Bootstrap** for responsive layout and interface styling. It also follows **shadcn/ui-inspired design principles** for a clean, modern, and professional interface, while remaining fully compatible with **Laravel Blade**.

The UI should always aim for:

- clean spacing
- consistent visual hierarchy
- readable typography
- polished forms and tables
- practical dashboard layout
- designer-quality admin interface presentation

---

## Database Design Rule

The project follows **database normalization review up to Third Normal Form (3NF)** when designing or revising tables.

This means:

- data should be structured to reduce redundancy
- repeated values should be separated into proper related tables when needed
- transitive dependencies should be removed
- table relationships should be clear and practical for Laravel migrations and Eloquent models

The goal is to keep the database organized, maintainable, and scalable.

---

## Project Structure Goal

The project follows standard **Laravel MVC architecture**:

- **Models** for data representation
- **Views** using Blade templates only
- **Controllers** for request handling and application flow
- **Migrations** for database schema management
- **Routes** for web request mapping

Reusable UI parts should be separated into **Blade layouts, includes, and components** whenever possible.

---

## Development Rules

- Always use **Laravel conventions**
- Use **Blade only**
- Do not use React, Vue, Inertia, Livewire, or Next.js
- Use **Bootstrap** as the main UI framework
- Apply **shadcn/ui-inspired design** only as visual inspiration adapted for Blade
- Keep business logic out of Blade templates as much as possible
- Prefer readable, maintainable, and scalable code
- Always check that logic is correct before finalizing
- Keep controllers and views from becoming too large
- Use Laravel validation and built-in security features
- Store sensitive configuration in `.env`
- Use role-based access for authorized users
- Use Laravel’s built-in password hashing for credentials
- Review database design in **3NF** and suggest normalized tables where appropriate

---

## Local Setup Guide

### Requirements

Make sure you have the following installed:

- PHP
- Composer
- MySQL
- XAMPP
- Node.js and npm
- Laravel-compatible environment

### Installation

1. Clone the repository.
2. Move the project into your `htdocs` folder if you are using XAMPP.
3. Open the project folder in Visual Studio Code.
4. Install PHP dependencies:

```bash
composer install
```

5. Install frontend dependencies:

```bash
npm install
```

6. Create a copy of the environment file:

```bash
cp .env.example .env
```

7. Generate the Laravel application key:

```bash
php artisan key:generate
```

8. Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meedocentrix
DB_USERNAME=root
DB_PASSWORD=
```

9. Run database migrations:

```bash
php artisan migrate
```

10. Start the development server:

```bash
php artisan serve
```

11. Compile frontend assets if needed:

```bash
npm run dev
```

If using XAMPP Apache directly, make sure Apache and MySQL are both running before testing the system.

---

## Suggested Testing Workflow

After making changes:

1. Review the logic for correctness
2. Test the affected pages in the browser
3. Check database interactions carefully
4. Verify form validation and role-based access
5. Review UI spacing, consistency, and responsiveness
6. Confirm reports, computations, and stored records are correct

---

## Security Notes

- Only authorized users should access the system
- Use authentication and role-based permissions
- Protect credentials using Laravel’s built-in hashing
- Validate all incoming requests
- Protect sensitive data and stored records
- Keep backups of the database regularly

---

## Future Improvements

Possible future enhancements may include:

- online payment integration
- improved analytics and charts
- enhanced export options
- mobile-friendly optimization
- notification features
- audit trail logging
- broader reporting and monitoring tools

---

## Project Status

This project is intended as a **web-based comprehensive data management system for MEEDO** and serves as the proposed solution for improving office operations, record management, and service monitoring.

---

## Authors

- Cabañero, Neil Denise T.
- Escarlan, Avrail Ann G.
- Regala, Khing Jay C.

---

## License

This project is for academic and system development purposes.

## System Preview

![MEEDOCentrix Dashboard](../Meedocentrix/flowchart.png)