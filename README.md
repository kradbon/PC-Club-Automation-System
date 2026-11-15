# GB CLUB

GB CLUB is a Laravel monolith for a gaming club: a multilingual public site, PC/room catalogue, customer accounts, reservation approvals, wallet ledger, food ordering, tournaments/teams, and protected operations tools. The site uses Blade, Tailwind CSS 4, Alpine.js, and Vite. Laravel 13 and PHP 8.3+ are required. MySQL 8 is the application database for local development and production.

The visual identity is original: dark arena surfaces, lime accents, strong type, responsive room cards, and gaming photography. The supplied room configurations and prices are **editable sample data**, not a statement of real club inventory or prices.

## Windows / Laragon setup

Open Laragon and start MySQL, then open PowerShell or Command Prompt in this directory. Enable PHP extensions `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, and `xml`; install PHP 8.3+, Composer 2, Node.js 20.19+ or 22.12+, and MySQL 8.

Create the database in Laragon or MySQL:

```sql
CREATE DATABASE gb_club CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Create `gb_club` in phpMyAdmin or with the SQL above. `copy .env.example .env` selects MySQL with Laragon's local defaults (`127.0.0.1:3306`, user `root`, blank password). Adjust these values in `.env` if your MySQL account differs. To use SQLite instead, set `DB_CONNECTION=sqlite` and `DB_DATABASE` to a SQLite file path.

```cmd
composer install
copy .env.example .env
php artisan key:generate
```

Then run:

```cmd
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. The root redirects to `/en`; `/ru` and `/tj` select Russian and Tajik. In phpMyAdmin, select the `gb_club` database to inspect the application tables. Account registration at `/en/join` stores customers in this database. Do not run `migrate:fresh` against a database with user data.

If Node packages cannot be downloaded, the repository has a small local CSS/JavaScript fallback so the public pages and basic navigation still render. The full Tailwind/Alpine bundle requires `npm install` and `npm run build`; the normal Vite dev server is preferred while building:

```cmd
php artisan serve
npm run dev
php artisan queue:work
```

Run those commands in separate terminals. For password reset links in local development, `MAIL_MAILER=log` writes the email to `storage/logs/laravel.log`.

## First administrator

Local and test environments seed three example accounts. Use these to open the role-specific pages:

| Role | Email | Password | Start page |
| --- | --- | --- | --- |
| Admin | `demo-admin@example.com` | `GBClubDemoAdmin!2026` | `/en/admin` |
| Staff | `demo-staff@example.com` | `GBClubDemoStaff!2026` | `/en/staff` |
| Customer | `demo-customer@example.com` | `GBClubDemoMember!2026` | `/en/account` |

Run `php artisan db:seed --class="Database\\Seeders\\DemoAccountSeeder"` to add them to an existing local database. The seeder does not change accounts that already use these email addresses, and it skips non-local/non-test environments. These are public demo credentials; do not deploy them or use them for real accounts.

Register a customer account at `/en/join`, then grant it admin access from the project directory:

```cmd
php artisan club:make-admin player@example.com
```

The role-specific admin dashboard is `/en/admin`; staff start at `/en/staff`. Staff booking operations are at `/en/staff/bookings`, PC status is at `/en/staff/pcs`, and food operations are at `/en/staff/food-orders`. Administrators can manage PC zones and status, approve bookings, manage users and roles, adjust wallets, inspect the financial ledger, edit the food catalogue at `/en/admin/food-menu`, manage discounts and promotions in Website & Community, and manage tournaments, events, reviews, and localized CMS pages. The dashboard reports confirmed/completed booking value plus approved hour packs; wallet top-ups are excluded to prevent double counting. Every wallet adjustment creates a ledger record with the actor and required reason; role, food catalogue, promotion, and content changes are audited.

## Main flows

- **Room catalogue:** Regular, VIP, Luxe, Streamer Room, Bootcamp DUST2, Bootcamp MIRAGE, two WINGMAN rooms, and two SOLO LUXE rooms are seeded into the same database inventory used by the public room/pricing pages. Each PC record includes sample CPU, GPU, RAM, display, refresh rate, and peripherals.
- **Reservations:** Customers choose a zone, a PC for station zones, a start time, and a duration. Requests begin `pending`. Staff/admin confirmation locks the booking/zone/PC, rechecks maintenance and overlapping confirmed reservations, snapshots the hourly/total price, and charges the member wallet. Customers can cancel pending bookings; confirmed bookings are refundable when cancelled at least two hours before the start. Staff cancellations refund the recorded cash/bonus split.
- **Wallets:** Cash and bonus balances are separate decimal values. Every adjustment writes to `wallet_transactions`; confirmation debits bonus first and cash second. The local payment adapter is explicitly a **simulation** and never represents a real card payment. It is disabled outside `local` and `testing`.
- **Food orders:** Customers order seeded, database-backed menu items for service to their club room/table or delivery to a home address. Prices are snapshotted at order time, delivery address/phone are required for delivery, and staff/admin process orders through the food queue. Orders are paid on arrival; this feature does not collect online payment.
- **Payments:** A signed HMAC webhook endpoint and idempotent payment ledger are provided for a future provider adapter. No real provider SDK/account is configured. Keep `PAYMENT_DRIVER` non-simulated in production until a real adapter is implemented and independently verified.
- **Tournaments:** Admins can create/edit tournaments in English, Russian, and Tajik. Solo registration is supported, and team tournaments require a captain-created team with enough members. Paid registration is deliberately rejected until wallet fee charging/refund rules are connected.
- **Languages:** Locale-prefixed URLs, user locale preference, interface dictionaries, and JSON-translated room/tournament/page content are used. English is the fallback for untranslated admin/validation strings.

## Environment settings

| Variable | Purpose |
| --- | --- |
| `APP_URL`, `APP_KEY`, `APP_DEBUG`, `APP_TIMEZONE` | URL, encryption, debug, and club timezone (`Asia/Dushanbe` by default) |
| `DB_*` | MySQL 8 connection; SQLite is supported for isolated tests |
| `CLUB_CURRENCY` | Currency code; sample records use TJS |
| `FOOD_DELIVERY_FEE_CENTS` | Optional delivery fee in cents; defaults to zero |
| `CLUB_OPENING_TIME`, `CLUB_CLOSING_TIME` | Booking window, including overnight hours |
| `BOOKING_MINIMUM_MINUTES`, `BOOKING_MAXIMUM_MINUTES`, `BOOKING_MAXIMUM_DAYS_AHEAD` | Reservation bounds |
| `BOOKING_MAXIMUM_ACTIVE_PER_USER`, `BOOKING_CANCELLATION_CUTOFF_MINUTES` | Customer limits and cancellation deadline |
| `BOOKING_BONUS_FIRST` | Whether bonus balance is consumed before cash |
| `PAYMENT_DRIVER`, `PAYMENT_WEBHOOK_SECRET`, `PAYMENT_SIMULATION_LIMIT` | Payment adapter selection, HMAC secret, and local simulation cap |
| `QUEUE_CONNECTION`, `CACHE_STORE`, `SESSION_DRIVER`, `MAIL_*` | Queue, cache, sessions, and outgoing mail configuration |

Use a long random webhook secret and HTTPS for any real payment integration. Never place card numbers or CVVs in this application. Configure a provider signature scheme before enabling a real payment adapter.

## Database and maintenance

```cmd
php artisan migrate --seed
php artisan db:seed
php artisan queue:work --tries=3
php artisan test
```

The seeder uses `updateOrCreate` for sample zones, PCs, room data, food menu, events, tariffs, a free CS2 tournament, and basic pages. It can be run again without duplicating those records. Add production staff by changing a registered user's role through the admin panel.

## Production deployment (Linux / Nginx)

1. Provision PHP 8.3+, Composer, Node, MySQL 8, and PHP-FPM with required extensions. Create a least-privilege MySQL application user and database.
2. Deploy the release, run `composer install --no-dev --optimize-autoloader`, `npm ci`, and `npm run build`.
3. Set production `.env`: `APP_ENV=production`, `APP_DEBUG=false`, a stable `APP_KEY`, HTTPS `APP_URL`, MySQL credentials, a real mail transport, `QUEUE_CONNECTION=database` or Redis, and a non-simulation payment driver only after a real adapter is configured.
4. Point the Nginx document root to `public/`, enable HTTPS, and deny access to dotfiles and all project files outside `public/`.
5. Run `php artisan migrate --force`, then `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`. Ensure `storage` and `bootstrap/cache` are writable by PHP-FPM.
6. Keep a supervised queue worker running (for example, Supervisor: `php artisan queue:work --sleep=3 --tries=3`). Booking status notifications are queued and write to the database and configured mail transport.
7. Back up MySQL and `storage`, monitor failed jobs, configure log rotation, and deploy schema changes through migrations. There are currently no scheduled club tasks; add a scheduler only when a scheduled action is implemented.

Minimal Nginx site configuration:

```nginx
server {
    listen 443 ssl http2;
    server_name club.example.com;
    root /var/www/gb-club/current/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```

## Verification and current limits

Run `php artisan test` for booking approval/conflict, wallet debit/refund, simulated top-up, signed/idempotent webhook, tournament team registration, auth, public pages, and admin access checks. The current suite passes on SQLite. The package registry was unavailable in this workspace (`npm` returned `ENOTCACHED`), so the Vite production build was not run here. The existing PHP session-auth screens cover registration, login, logout, and password resets; Laravel Fortify/Breeze was not installed in this checkout. Complete payment-provider integration, paid tournament fee settlement, promotion redemption, match bracket/result workflows, and broad admin CRUD for every requested module remain follow-up work before treating this as a finished production platform.

## Project structure

- `app/Http/Controllers` — public, account, staff, admin, and webhook controllers
- `app/Http/Requests` — account, booking, wallet, and tournament validation
- `app/Services` — booking rules, wallet ledger, payments, and audit logging
- `app/Models` and `app/Policies` — database relationships and booking authorization
- `resources/views` — Blade layouts, public pages, member cabinet, and admin/staff views
- `resources/css/app.css`, `resources/js/app.js`, `vite.config.js` — Tailwind, Alpine, and Vite sources
- `database/migrations`, `database/seeders` — schema and editable sample records
- `lang/{en,ru,tj}` — customer-facing interface strings with fallback behavior
