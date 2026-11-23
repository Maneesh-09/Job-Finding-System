# Job Finding System

Requires PHP 8+ with mysqli, mbstring and fileinfo, and MySQL/MariaDB.

Start MySQL and create a database named `jfc`. For a fresh installation, import
`allsql.sql` into that empty database. For an existing installation, back up the
database and run `php migrate.php` once, or select your existing `jfc` database
in phpMyAdmin and import `update_existing_database.sql` using the **Import** tab.
Both options apply the same migration. It adds missing company/profile fields,
supports custom skills, widens application fields, creates missing application
archive tables, renames the older `users`/`experience` names, and reconnects old
jobs to their companies. Existing accounts, passwords, jobs and applications
are retained; missing nullable text is normalized to an empty string. You can
rerun it. Do not import `allsql.sql` over an existing database.

The SQL import needs permission to create/drop a stored procedure and alter
tables. If both `user` and `users` tables already exist, the migration stops
with a message so you can resolve which table contains the active accounts.
Schema changes commit individually; take a backup before running the migration.

The default connection is `127.0.0.1:3336`, database `jfc`, user `root`, with an
empty password. Override it with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and
`DB_PASSWORD` environment variables. For a standard XAMPP installation using
port 3306, set `DB_PORT=3306`. PowerShell example:

```powershell
$env:DB_PORT = '3306'
php migrate.php
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000`. On a fresh database, use `firstadminsetup.php` to
create the first administrator. Once an admin exists, only a signed-in admin
can create additional admins. User and company registration have separate forms.

Job seekers can select skills and add comma-separated custom skills when
registering or editing their profile. Saved skills appear on the profile;
uncheck a skill to remove it. The dashboard and **Jobs for My Skills** show
unexpired jobs whose title, description, qualification or category mentions a
saved skill, ranked by the number of matching skills. Each card identifies its
matches. **View Vacant Jobs** also offers all jobs and category filtering.

The web server needs write access to `images/` and `uploads/`. Uploads accept
JPG/PNG images and PDF CVs up to 5 MB. Existing company uploads are preserved.

Run the integration checks against an isolated temporary MariaDB instance:

```powershell
python tests/integration.py --mysql-bin C:/xampp/mysql/bin
```

The checks create their own database directory and PHP server and shut both
down afterwards. They do not connect to your application database.
