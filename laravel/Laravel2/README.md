# Novelle membership workspace

This Laravel 12 project has two sign-in roles and a public membership application form.

| Role | Access |
| --- | --- |
| Member | View posted tasks and the member directory. |
| Admin | Create, view, edit, complete, and delete tasks; review applications; approve or reject applications; manage member profiles, categories, and levels. |

Approving an application creates a pending member profile and a membership application transaction with the default lowest-fee membership level, then sends an approval notice. Record full payment and approve the transaction to activate the membership. If the new account still needs a password, its first sign-in attempt sends a one-time password setup link to the member's email. Existing member profiles without a login can be given one by editing the profile.

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

## Email, maps, and weather

The default mailer sends queued messages to a local Mailpit SMTP server at `127.0.0.1:1025`; start Mailpit and view captured messages at `http://127.0.0.1:8025`. Mailpit is a local testing inbox and does not deliver to external email accounts. Run `php artisan queue:work` in a second terminal to process queued messages. To deliver to a real inbox, configure your provider's SMTP host and credentials in `.env` (for Gmail, use an App Password). This Laravel version reads `MAIL_SCHEME` for SMTP scheme settings.

Set `GOOGLE_MAPS_KEY` to a browser key restricted to your site referrer and the Maps JavaScript API. Set `OPENWEATHER_KEY` to your OpenWeather server key. Run `php artisan config:clear` after changing either key. Without the Google key, applicants can still enter coordinates manually; without the OpenWeather key, the weather panel reports that weather is unavailable. Do not commit `.env`.

## Tests

```powershell
php artisan test
```
