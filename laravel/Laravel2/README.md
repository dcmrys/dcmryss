# Novelle Tasks and Membership Management

A burgundy task list and membership management app. The navigation has four pages: **Tasks**, **Members**, **Membership Categories**, and **Membership Levels**.

## Features

- Add, complete, reopen, and remove tasks
- Register, view, edit, delete, deactivate, and reactivate members
- Search members by name, category, level, and status
- Choose a provided category and membership level from dropdowns when registering or editing a member
- See each level's fee, validity period, and benefits on the member form
- Add, edit, and remove unused membership categories and levels on their own pages

The provided categories are Student, Regular, Senior Citizen, and Corporate.

| Level | Fee | Validity | Benefits |
| --- | ---: | ---: | --- |
| Basic | ₱500 | 6 months | Digital member ID; general events |
| Silver | ₱1,000 | 12 months | Basic benefits; discounted event registration; newsletters |
| Gold | ₱2,000 | 18 months | Silver benefits; priority event registration; exclusive workshops |
| Platinum | ₱3,500 | 24 months | Gold benefits; VIP events; priority member support |

Registering a member starts their membership immediately using the selected level's validity period. Changing the level starts a new validity period on the date of the change. Fees are shown for reference; the app does not collect payment.

## Run locally

```powershell
composer install
npm install
Copy-Item .env.example .env
New-Item -ItemType File database/membership.sqlite
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open [Tasks](http://127.0.0.1:8000/tasks), [Members](http://127.0.0.1:8000/members), [Membership Categories](http://127.0.0.1:8000/categories), or [Membership Levels](http://127.0.0.1:8000/levels).

## Test

```powershell
php artisan test
```
