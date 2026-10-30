# baseNEXA PMS

**A multi-company Property Management System for hotels and vacation villas.**

baseNEXA PMS lets a platform owner (Super Admin) onboard client companies. Each company then manages its own hotels and villas, rooms, guests and availability from one dashboard, with its data fully separated from every other company.

> **Project status:** under active development. The core platform, property and inventory management, guests and availability search are done. Reservations, pricing, finance and integrations are planned (see the [Roadmap](#roadmap)).

---

## Table of contents

- [Features](#features)
- [User roles](#user-roles)
- [Tech stack](#tech-stack)
- [Getting started](#getting-started)
- [Running the project](#running-the-project)
- [Main routes](#main-routes)
- [Project structure](#project-structure)
- [Design decisions](#design-decisions)
- [Roadmap](#roadmap)
- [Screenshots](#screenshots)
- [Author](#author)

---

## Features

### Platform (Super Admin)
- Role-based dashboard and navigation
- Company management: create, edit, activate and deactivate companies
- Company Admin accounts: create an admin and assign the admin to an active company
- Inactive companies are blocked: their admins are logged out with a clear message
- Public registration is disabled. Accounts are created only by the Super Admin

### Properties (Company Admin)
- Add, edit, list, and activate or deactivate **hotels and villas**
- Address, contact details, country, timezone and currency for every property
- New properties start with the company's default currency and timezone
- Card view with live search and filters by name, city, email, type and status
- Summary cards: total properties, hotels, villas and active properties

### Rooms, units and inventory
- **Room types** for hotels with max adults, max children and capacity rules
- **Individual hotel rooms** under a room type (capacity comes from the room type)
- **Villa units** as independently bookable units with their own capacity
- Room and unit statuses: Available, Out of Service and Maintenance, changed straight from the list
- **Maintenance and owner-use blocks** for date ranges, with overlap checks, Current / Past / All tabs and an Active now / Upcoming / Completed state
- "Blocked today" indicator, live search and filters, and summary cards

### Guests
- Guest profiles with contact details, date of birth, address, preferences and internal notes
- Live search by name, email or phone
- Duplicate email protection inside a company
- Guest profile page with a reservation history section (filled once reservations are added)

### Availability search
- Search by property, check-in, check-out, adults and children; results update as soon as the fields are filled
- Hotels: available rooms grouped by room type. Villas: available units
- Not available list with the reason for each room: maintenance block, out of service, inactive room type or too small for the guests
- The check-out day is free for the next guest

### Security
- Company data isolation: every tenant-owned table has a `company_id`
- Laravel Gates and Policies on every module, so a company can never open another company's records (403)
- `company_id` is always set by the server and is never accepted from a form
- Server-side validation on every form
- Soft deletes and deactivate-instead-of-delete, so history stays safe

---

## User roles

| Role | Access |
|---|---|
| **Super Admin** | Manages companies and Company Admin accounts for the whole platform |
| **Company Admin** | Manages the properties, rooms, guests and availability of their own company |
| Property Manager, Receptionist, Finance, Housekeeping | Planned |

---

## Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel |
| Database | MySQL |
| Frontend | Blade, Tailwind CSS, JavaScript |
| Build tool | Vite |
| Authentication | Laravel authentication (Breeze) with role-based Gates |
| Local environment | Windows + XAMPP |

---

## Getting started

### Requirements

- PHP 8.3 or higher
- Composer
- Node.js and npm
- MySQL (XAMPP, Laragon or similar)
- Git

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/MayankSaini001/property-management-system.git
cd property-management-system

# 2. Install PHP and JavaScript dependencies
composer install
npm install

# 3. Create the environment file and the application key
cp .env.example .env
php artisan key:generate
```

On Windows Command Prompt use `copy .env.example .env` instead of `cp`.

### Database

1. Create an empty MySQL database, for example `pms_db`.
2. Open `.env` and set your database details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pms_db
DB_USERNAME=root
DB_PASSWORD=
```

3. Create the tables and the Super Admin account:

```bash
php artisan migrate
php artisan db:seed --class=SuperAdminSeeder
```

The Super Admin email and password are defined in `database/seeders/SuperAdminSeeder.php`. **Change the password after your first login, and never keep the default password on a live server.**

---

## Running the project

Open two terminals in the project folder.

```bash
# Terminal 1: the Laravel server
php artisan serve

# Terminal 2: the frontend build with live reload
npm run dev
```

Then open [http://127.0.0.1:8000](http://127.0.0.1:8000).

> On Windows PowerShell, `npm` can be blocked by the execution policy. Use `npm.cmd run dev` instead.

For a production build of the CSS and JavaScript:

```bash
npm run build
```

### Creating your first company

1. Log in as the Super Admin.
2. Open **Companies** and add a company.
3. Open **Company Admins** and create an admin for that company.
4. Log in as the Company Admin and start with **Properties**: add a property, then its room types and rooms (hotel) or units (villa), then guests.

---

## Main routes

| Area | URL |
|---|---|
| Login | `/login` |
| Dashboard | `/dashboard` |
| Companies (Super Admin) | `/superadmin/companies` |
| Company Admins (Super Admin) | `/superadmin/company-admins` |
| Properties | `/company/properties` |
| Room types (hotels) | `/company/properties/{property}/room-types` |
| Rooms and units | `/company/properties/{property}/units` |
| Maintenance and blocks | `/company/properties/{property}/blocks` |
| Guests | `/company/guests` |
| Availability search | `/company/availability` |

Run `php artisan route:list` for the complete list.

---

## Project structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── SuperAdmin/          Companies and Company Admins
│   │   └── Company/             Properties, room types, units, blocks, guests, availability
│   ├── Middleware/
│   │   └── EnsureCompanyIsActive.php
│   └── Requests/
├── Models/                      Company, User, Property, RoomType, Unit, InventoryBlock, Guest
├── Policies/                    One policy per module (company data isolation)
└── Providers/
    └── AppServiceProvider.php   Gates: isSuperAdmin, isCompanyAdmin
database/
├── migrations/
└── seeders/                     SuperAdminSeeder
resources/views/
├── layouts/                     App layout, navigation, guest layout
├── superadmin/                  Super Admin pages
└── company/                     properties, room-types, units, blocks, guests, availability
routes/
├── web.php
└── auth.php
```

---

## Design decisions

- **One database, many companies.** Every tenant-owned table carries a `company_id`. Records are always read and written through the logged-in user's company, and policies check ownership again on every request.
- **Deactivate instead of delete.** Companies, properties and room types are deactivated, and rooms use statuses. This keeps booking history intact once reservations exist.
- **Room type and room are different things.** A hotel has room types (for example Deluxe Room) and individual rooms (101, 102). A villa is a single bookable unit. This keeps availability and pricing accurate for both.
- **Date rules.** A stay covers the nights from check-in to the day before check-out, so the check-out day is free for the next guest. A block covers every night from its start date to its end date, both included.
- **Reusable availability rules.** `Unit::bookable()` and `Unit::freeBetween($checkIn, $checkOut)` are shared scopes, so reservations and the search use the same logic.

---

## Roadmap

- [x] Project setup, authentication and role-based access
- [x] Super Admin: companies and Company Admin accounts
- [x] Company-wise data isolation with policies
- [x] Property management (hotels and villas)
- [x] Room types, rooms, villa units and inventory statuses
- [x] Maintenance and owner-use blocks
- [x] Guest management
- [x] Availability search
- [ ] Reservations and booking calendar, with double-booking prevention
- [ ] Rate plans, pricing rules and restrictions
- [ ] Front desk: arrivals, departures, check-in and check-out
- [ ] Housekeeping and maintenance requests
- [ ] Payments, folios and invoices
- [ ] Notifications
- [ ] Reports and exports
- [ ] Staff roles and permissions (Property Manager, Receptionist, Finance, Housekeeping)
- [ ] External integrations (PMS, OTA and channel platforms, one provider at a time)
- [ ] Automated tests and production deployment

---

## Screenshots

<!--
Add your screenshots to docs/screenshots/ and link them here, for example:

![Login](docs/screenshots/login.png)
![Properties](docs/screenshots/properties.png)
![Rooms](docs/screenshots/rooms.png)
![Availability](docs/screenshots/availability.png)
-->

_Screenshots will be added soon._

---

## Author

**Mayank Saini** ([@MayankSaini001](https://github.com/MayankSaini001))

baseNEXA by Baseline.