# Deploy Novelle on Render

This project can be deployed as a Docker web service with a Render Postgres database.

## Create the database and web service

1. Push this repository to GitHub and create a Render Postgres database. Choose a **paid database plan** if the data must remain available beyond the free database's 30-day lifetime.
2. Create a Render **Web Service** from the repository.
3. Set **Root Directory** to `laravel/Laravel2` (relative to the repository root), **Language** to `Docker`, **Dockerfile Path** to `Dockerfile`, and **Docker Build Context Directory** to `.`.
4. Set the service health check path to `/up`.
5. In the web service's Environment settings, add the values below. Copy the database's **internal** connection URL into `DB_URL`.

| Key | Value |
| --- | --- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Generate locally with `php artisan key:generate --show`; save the output as a secret in Render. |
| `APP_URL` | `https://<your-service>.onrender.com` |
| `ASSET_URL` | `https://<your-service>.onrender.com` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | The Render Postgres internal connection URL. |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `RUN_MIGRATIONS` | `true` |
| `RUN_DB_SEEDER` | `true` for the first deploy, then change it to `false`. |
| `ADMIN_EMAIL` | The email for the production admin account. |
| `ADMIN_PASSWORD` | A new, strong password for that account; store it as a secret in Render. |

The container runs migrations at startup. On the first deploy, the seeder creates the configured admin account and the default membership categories and levels. Running the seeder again updates the admin account's email, password, name, and role from the current `ADMIN_EMAIL` and `ADMIN_PASSWORD`; turn `RUN_DB_SEEDER` off after the initial seed or a deliberate admin credential reset.

`QUEUE_CONNECTION=sync` processes notification emails during the request, so a separate worker service is not required. Configure `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and `MAIL_FROM_ADDRESS` with an external mail provider. Free Render web services block outbound SMTP ports 25, 465, and 587; use a provider that supports another port such as 2525, or a paid service plan.

The admin test-credentials panel is restricted to `APP_ENV=local`, so it will not be shown on the production site. Local MySQL records are not copied automatically; migrate or import any data you want to keep into the Render Postgres database.
