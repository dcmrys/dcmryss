# Customer account dashboard

This CodeIgniter 4 app uses the `electric_company` MySQL database. The supplied account data is in `database/electric_company.sql`. Login accounts are stored separately in `auth_users`; customer account records are not login credentials.

## First run on XAMPP

1. Start Apache and MySQL.
2. Create a database named `electric_company` in phpMyAdmin, then import `database/electric_company.sql` into it.
3. From this directory, run `php spark migrate` to create the login table. Use your XAMPP PHP executable if `php` is not on your PATH.
4. On the same computer, open `http://localhost/dcmryss/ci4_pagination/setup` and create the first admin account. The setup page closes after that account is created.
5. Sign in at `http://localhost/dcmryss/ci4_pagination/login`. Successful login opens `/dashboard`.

The database and migration have already been set up in this local XAMPP installation. Create your admin account at the setup URL to start using the dashboard.

If this app is moved to another URL, update `$baseURL` in `app/Config/App.php` and the database settings in `app/Config/Database.php`.
