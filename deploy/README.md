# Deploying menyangbo.org on cPanel

`deploy/deploy.sh` updates the live site from GitHub (`saddy1/menyangbo.org`, branch `main`)
**without touching uploaded images, the `.env` file or the database data**.

## What the script does

1. Clones or updates the repo in a separate folder (`~/menyangbo-repo`). The live site is never a git checkout.
2. Backs up the database (`mysqldump`, using the live `.env`) and the uploaded images to `~/menyangbo-backups/`.
3. Puts the site in maintenance mode.
4. Copies the code into the live folder with `rsync`. These are **never overwritten or deleted**:
   - `.env`, `storage/`, `vendor/`
   - uploads: `public/photos`, `public/notices`, `public/events`, `public/committee-photos`,
     `public/banners`, `public/media`, `public/popups`, `public/storage`
   - cPanel files: `.htaccess`, `.user.ini`, `php.ini`, `error_log`, `.well-known/`, `cgi-bin/`
5. Runs `composer install --no-dev`. Composer is downloaded to `~/composer.phar` if it isn't installed.
6. Runs `php artisan migrate --force` to apply new database changes to the existing data.
7. Rebuilds the caches (`config:cache` and `view:cache`) and takes the site out of maintenance mode.

The built CSS/JS (`public/build`) is in git, so **no Node/npm is needed on the server**.

## Requirements

- SSH access, or cPanel → **Terminal**.
- **PHP 8.2, 8.3 or 8.4**. The locked packages don't support 8.1 or 8.5. Set it in cPanel → MultiPHP Manager
  (and for the command line, pass `PHP=/opt/cpanel/ea-php83/root/usr/bin/php` if plain `php` is a different version).
- `git`, `rsync` and `mysqldump`. These are available on most cPanel hosts.

## First-time setup

1. **Find the live app folder**: the folder that contains `artisan` and `.env`. For example `~/menyangbo`,
   or `~/public_html` if the whole app sits there.
2. **Find where the site is served from**:
   - If the domain's document root is the app's `public/` folder, or the app itself is in `public_html` with
     a root `.htaccess` → nothing extra is needed.
   - If `~/public_html` holds a copy of Laravel's `public/` folder (with an edited `index.php` pointing to the app),
     set `PUBLIC_DIR=$HOME/public_html` so CSS/JS updates also go there. `index.php` and `.htaccess` there are kept.
3. **Back up once by hand**, just in case: cPanel → Backup (or phpMyAdmin → Export) and download `public/photos`.
4. **Get the script onto the server** and do a dry run first. It only lists what would change:

   ```bash
   git clone https://github.com/saddy1/menyangbo.org.git ~/menyangbo-repo
   APP_DIR=$HOME/menyangbo DRY_RUN=1 bash ~/menyangbo-repo/deploy/deploy.sh
   ```

   Lines starting with `*deleting` are files on the server that are not in the repo. Check that none of them are
   uploads you want to keep. If a folder holds uploads that aren't listed above, tell the developer so it can be added to
   `UPLOAD_DIRS` in the script.
5. **Deploy**:

   ```bash
   APP_DIR=$HOME/menyangbo bash ~/menyangbo-repo/deploy/deploy.sh
   ```

   To avoid typing the settings every time, create `~/deploy-menyangbo.sh`:

   ```bash
   #!/bin/bash
   export APP_DIR=$HOME/menyangbo          # live app folder
   # export PUBLIC_DIR=$HOME/public_html   # only if public/ is copied into public_html
   # export PHP=/opt/cpanel/ea-php82/root/usr/bin/php
   bash $HOME/menyangbo-repo/deploy/deploy.sh "$@"
   ```

   Then every deploy is just `bash ~/deploy-menyangbo.sh`. The script fetches the latest `main` itself.

## Database changes applied by this release

`php artisan migrate` runs these on the live data. The database is backed up first.

| Migration | Effect |
|---|---|
| `2026_09_25_120000_add_birth_order_to_persons_table` | new `birth_order` column (सन्तान क्रम) |
| `2026_09_25_130000_add_link_parent_to_person_change_requests_type` | allows the `link_parent` and `not_listed` request types |
| `2026_09_25_140000_fix_nepali_calendar_month_days` | corrects BS month lengths for 2080–2084 (today's date was one day behind) |
| `2026_09_25_150000_fill_member_type` | fills सदस्यको प्रकार: दाजुभाइ / दिदीबहिनी / बुहारी (empty values only) |

Optional `.env` setting: `SEO_URL=https://menyangbo.org` (the public URL used in SEO tags and the sitemap).

## If something goes wrong

- Deploy stopped halfway → fix the error shown, then `cd $APP_DIR && php artisan up`.
- Restore the database: `gunzip < ~/menyangbo-backups/db-YYYYMMDD-HHMMSS.sql.gz | mysql -u USER -p DBNAME`.
- Restore images: `tar -xzf ~/menyangbo-backups/uploads-public-YYYYMMDD-HHMMSS.tar.gz -C $APP_DIR/public`.
- Go back to older code: `REF=<old-commit> bash ~/deploy-menyangbo.sh`. Note that database migrations are not undone
  automatically. Restore the database backup if needed.
