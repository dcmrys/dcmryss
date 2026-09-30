# Puihaha Electric CI4 app

`demonstration_ci4` now contains the site, registration page, and customer account dashboard. The Login button opens the dashboard in this same app. It is a navigation button; the dashboard does not require credentials.

## XAMPP setup

1. Start Apache and MySQL.
2. Create one database named `electriccompany`.
3. Import `database/users.sql` if the `users` table does not already exist. Import `database/customer_accounts.sql` if the `customer_accounts` table does not already exist. The account file includes sample data.
4. Open `http://localhost/dcmryss/demonstration_ci4/public/index.php/login` and click **Login**. The account dashboard is also available at `http://localhost/dcmryss/demonstration_ci4/public/index.php/dashboard`.

The app uses the `electriccompany` database configured in `app/Config/Database.php`. If the app is moved or your MySQL settings differ, update that file and `$baseURL` in `app/Config/App.php`.

The former pagination app's dashboard is part of this project; only `demonstration_ci4` is needed.
