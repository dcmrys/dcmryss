# Customer account dashboard

This CodeIgniter 4 app uses the `electric_company` MySQL database. The supplied account data is in `database/electric_company.sql`.

## First run on XAMPP

1. Start Apache and MySQL.
2. Create a database named `electric_company` in phpMyAdmin, then import `database/electric_company.sql` into it.
3. Open `http://localhost/dcmryss/demonstration_ci4/public/index.php/login` and click **Login** to open the dashboard in `ci4_pagination`.

The dashboard does not require an account or password.

If this app is moved to another URL, update `$baseURL` in `app/Config/App.php` and the database settings in `app/Config/Database.php`.
