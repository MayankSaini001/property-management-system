# baseNEXA PMS

Multi-company Property Management System for hotels and vacation villas.

## Tech stack
Laravel, MySQL, Blade, Tailwind CSS

## Local setup
1. `composer install`
2. `npm install`
3. `.env.example` ko copy karke `.env` banao aur database set karo
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed --class=SuperAdminSeeder`
7. `npm run dev` aur `php artisan serve`