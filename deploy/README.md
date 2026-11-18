# Deploying menyangbo.org (cPanel)

```bash
bash ~/menyangbo-repo/deploy/deploy.sh              # update code only — database not touched
MIGRATE=1 bash ~/menyangbo-repo/deploy/deploy.sh    # also run migrations (DB backed up first)
```

What it does:

1. Gets the latest `main` from GitHub into `~/menyangbo-repo`.
2. Puts the site in maintenance mode.
3. Copies the code into the live site `~/menyangbo.org`.
   Never touched: `.env`, `storage/`, `vendor/`, uploaded files in `public/`, `.htaccess`.
4. Runs `composer install` and `php artisan package:discover`.
5. Only with `MIGRATE=1`: backs up the database to `~/menyangbo-backups/`, then runs migrations.
6. Rebuilds caches and brings the site back up. If any step fails, the site is brought back up automatically.

Settings (optional): `APP_DIR` (live site folder), `PHP` (e.g. `/opt/cpanel/ea-php83/root/usr/bin/php`), `BRANCH`.

Shortcut: put this in `~/deploy.sh` and run `bash ~/deploy.sh`:

```bash
#!/bin/bash
bash ~/menyangbo-repo/deploy/deploy.sh "$@"
```

Restore a database backup: `gunzip < ~/menyangbo-backups/db-XXXX.sql.gz | mysql -u USER -p DBNAME`
