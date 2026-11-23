# Novelle membership workspace

This Laravel 12 project has two sign-in roles and a public membership application form.

| Role | Access |
| --- | --- |
| Member | View posted tasks and the member directory. |
| Admin | Create, view, edit, complete, and delete tasks; review applications; approve or reject applications; manage member profiles, categories, and levels. |

Approving an application creates a pending member profile and a membership application transaction with the default lowest-fee membership level, then sends an approval notice with a one-time password setup link for new member accounts. Record full payment and approve the transaction to activate the membership. Existing member profiles without a login can be given one by editing the profile.

## Transaction processing

The admin Transactions page lists application, registration, and renewal transactions with unique `TXN-` references, balances, and Pending, Paid, Approved, Expired, or Cancelled statuses. Start renewals from that page, record partial or full payments with unique `PAY-` numbers on a transaction, then approve fully paid transactions to apply membership dates. The Payments page lists the payment ledger. Members have a **My Transactions** page for their own charges and payment history; admins can view each member's history on the member profile.

The application form at `/membership/apply` saves a pending application with a unique reference number, queues an email receipt, lets applicants select map coordinates, and displays current weather. The weather API key stays on the server. This email is a receipt, as in the supplied tutorial; it does not prove ownership of the email address.

## Set up

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Start MySQL in XAMPP and create a database named `membership` in phpMyAdmin before migrating. The sample `.env` uses `root` with an empty password; update the credentials there if your MySQL setup differs.

In `.env`, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` before seeding. For a local demo member, also set `DEMO_MEMBER_EMAIL` and `DEMO_MEMBER_PASSWORD`. The demo member is seeded only when `APP_ENV=local`. Existing records and passwords are preserved when the seeder runs again.

```powershell
php artisan migrate --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open `/login` for admin or member sign-in. The admin can add further member accounts from the Members page.

## InfinityFree deployment

The deployment ZIP does not include the local `.env`, `node_modules`, logs, or generated Laravel cache files. Its Composer autoloader is not optimized so InfinityFree will accept the generated PHP files. Keep a separate `.env` on the host with a valid `APP_KEY` and these production settings:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://novelle.xo.je
DB_CONNECTION=mysql
DB_HOST=your-mysql-hostname
DB_PORT=3306
DB_DATABASE=your-full-database-name
DB_USERNAME=your-hosting-database-username
DB_PASSWORD=your-hosting-database-password
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

Get the MySQL hostname, full database name, username, and password from your InfinityFree account. The local XAMPP values (`127.0.0.1`, `root`, and `membership`) will not connect from InfinityFree. If `DB_URL` is set, remove it so it does not override the individual database settings. Import the database schema and required data, including the `users` table and an admin account, through phpMyAdmin; InfinityFree does not provide a terminal for `php artisan migrate` or `db:seed`. `MAIL_MAILER=log` prevents delivery attempts until an SMTP provider is configured.

See [InfinityFree's Laravel deployment guide](https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578) for the web root rewrite and file upload instructions.

## Email, maps, and weather

The default mailer sends queued messages to a local Mailpit SMTP server at `127.0.0.1:1025`; start Mailpit and view captured messages at `http://127.0.0.1:8025`. Mailpit is a local testing inbox and does not deliver to external email accounts. Run `php artisan queue:work` in a second terminal to process queued messages. To deliver to a real inbox, configure your mail provider's SMTP host and credentials in `.env`.

The membership form uses Leaflet with OpenStreetMap tiles and Open-Meteo for current weather. Neither requires an API key or billing setup for this non-commercial project. The map includes OpenStreetMap attribution; follow the [tile usage policy](https://operations.osmfoundation.org/policies/tiles/) if you deploy it. Open-Meteo’s [free API terms](https://open-meteo.com/en/pricing) currently limit free access to non-commercial use and 10,000 calls per day; the weather response is cached for ten minutes.

## Tests

```powershell
php artisan test
```
