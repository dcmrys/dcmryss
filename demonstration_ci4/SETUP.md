# Puihaha Electric CI4 app

`demonstration_ci4` contains the site, customer registration, and customer account dashboard. Staff dashboard access requires a login account in the `user_accounts` table. The public registration page uses the separate `users` table and does not grant dashboard access.

## XAMPP setup

1. Start Apache and MySQL.
2. Create one database named `electriccompany`.
3. Import `database/users.sql`, `database/customer_accounts.sql`, and `database/user_accounts.sql` for any tables that do not already exist. The customer account file includes sample records.
4. From the `demonstration_ci4` directory, run `php spark dashboard:user` to create a staff dashboard username and password. Running it again for the same username resets that password. Passwords are stored as hashes.
5. Open `http://localhost/dcmryss/demonstration_ci4/public/index.php/login` and sign in. The dashboard is at `http://localhost/dcmryss/demonstration_ci4/public/index.php/dashboard`.

The app uses the `electriccompany` database configured in `app/Config/Database.php`. If the app is moved or your MySQL settings differ, update that file and `$baseURL` in `app/Config/App.php`.

The dashboard supports creating, viewing, editing, searching, and deleting customer accounts. It uses a session for login and requires a CSRF token for form submissions.
