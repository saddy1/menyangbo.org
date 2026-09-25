#!/usr/bin/env bash
#
# Deploy menyangbo.org on cPanel from GitHub, keeping the live uploads, .env and database.
#
#   bash deploy.sh               deploy the latest main branch
#   DRY_RUN=1 bash deploy.sh     only show which files would change / be deleted
#   REF=abc1234 bash deploy.sh   deploy a specific (e.g. older) commit instead of the latest
#
# How it works: the repo is cloned into a separate folder ($REPO_DIR) and the code is
# rsync'ed into the live app ($APP_DIR). Uploaded files, .env, storage/ and vendor/ are
# excluded, so rsync never overwrites or deletes them. The database is dumped before
# migrations run. See deploy/README.md for first-time setup.

set -euo pipefail

# This file lives inside the repo folder that step 1 updates; run from a temp copy so the
# update can't change the script while bash is still reading it.
if [ -z "${DEPLOY_SELF_COPY:-}" ]; then
  self_copy="$(mktemp)"; cp "$0" "$self_copy"
  DEPLOY_SELF_COPY="$self_copy" exec bash "$self_copy" "$@"
fi
trap 'rm -f "$DEPLOY_SELF_COPY"' EXIT

# ── Settings (override with environment variables) ─────────────────────────────
APP_DIR="${APP_DIR:-$HOME/menyangbo}"                  # live Laravel folder (has artisan + .env)
PUBLIC_DIR="${PUBLIC_DIR:-}"                           # set ONLY if the site is served from a separate
                                                       # folder like ~/public_html (not $APP_DIR/public)
REPO_URL="${REPO_URL:-https://github.com/saddy1/menyangbo.org.git}"
REPO_DIR="${REPO_DIR:-$HOME/menyangbo-repo}"
BRANCH="${BRANCH:-main}"
REF="${REF:-}"                                         # optional commit/tag to deploy (rollback)
PHP="${PHP:-php}"                                      # e.g. /opt/cpanel/ea-php82/root/usr/bin/php
COMPOSER="${COMPOSER:-}"                               # auto-detected when empty
COMPOSER_FLAGS="${COMPOSER_FLAGS:-}"                   # extra flags, e.g. --ignore-platform-req=php
BACKUP_DIR="${BACKUP_DIR:-$HOME/menyangbo-backups}"
BACKUP_UPLOADS="${BACKUP_UPLOADS:-1}"                  # 1 = also tar the upload folders before deploying
KEEP_BACKUPS="${KEEP_BACKUPS:-10}"
DRY_RUN="${DRY_RUN:-0}"

# Folders under public/ that hold uploaded content (never touched by the deploy)
UPLOAD_DIRS=(photos notices events committee-photos banners media popups storage)

say()  { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m!! %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31mxx %s\033[0m\n' "$*" >&2; exit 1; }

# ── Checks ──────────────────────────────────────────────────────────────────────
command -v git   >/dev/null || die "git not found"
command -v rsync >/dev/null || die "rsync not found (ask hosting support, or deploy with cPanel Git Version Control)"
[ -f "$APP_DIR/artisan" ] || die "No Laravel app at APP_DIR=$APP_DIR (set APP_DIR=/path/to/live/app)"
[ -f "$APP_DIR/.env" ]    || die "$APP_DIR/.env missing — the live .env must stay on the server"
"$PHP" -v >/dev/null 2>&1 || die "PHP not runnable: $PHP"
# composer.lock needs PHP 8.2 – 8.4 (symfony 7 wants ≥ 8.2, nette/schema allows ≤ 8.4)
"$PHP" -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") && version_compare(PHP_VERSION, "8.5.0", "<") ? 0 : 1);' \
  || [ -n "$COMPOSER_FLAGS" ] \
  || die "PHP 8.2–8.4 needed ($("$PHP" -r 'echo PHP_VERSION;') found). Pick it in cPanel → MultiPHP Manager, or run with PHP=/opt/cpanel/ea-php83/root/usr/bin/php"

STAMP="$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

# ── 1. Get the code ────────────────────────────────────────────────────────────
say "Fetching $BRANCH from $REPO_URL"
if [ -d "$REPO_DIR/.git" ]; then
  git -C "$REPO_DIR" fetch --quiet origin "$BRANCH"
else
  git clone --quiet --branch "$BRANCH" "$REPO_URL" "$REPO_DIR"
fi
# REPO_DIR is a clean mirror that is never edited by hand
git -C "$REPO_DIR" reset --quiet --hard "${REF:-origin/$BRANCH}"
echo "Commit: $(git -C "$REPO_DIR" log -1 --format='%h %s (%cr)')"

# ── rsync rules: what must never be overwritten or deleted on the server ───────
EXCLUDES=(
  --exclude=/.git/ --exclude=/.github/ --exclude=/node_modules/ --exclude=/tests/
  --exclude=/.env --exclude=/storage/ --exclude=/vendor/
  --exclude='/bootstrap/cache/*.php'
  # hosting files cPanel creates next to the app
  --exclude=/public/.htaccess --exclude=/.htaccess --exclude=.user.ini --exclude=php.ini
  --exclude=error_log --exclude=/.well-known/ --exclude=/cgi-bin/
)
for d in "${UPLOAD_DIRS[@]}"; do EXCLUDES+=("--exclude=/public/$d/"); done

RSYNC_OPTS=(-a --delete --itemize-changes)
[ "$DRY_RUN" = "1" ] && RSYNC_OPTS+=(--dry-run)
RSYNC_LOG="$(mktemp)"
trap 'rm -f "$DEPLOY_SELF_COPY" "$RSYNC_LOG"' EXIT
# run rsync (a failure stops the deploy), then list only real changes: new/updated/deleted files
sync_dir() {
  rsync "${RSYNC_OPTS[@]}" "$@" > "$RSYNC_LOG"
  grep -v '^\.[^ ]* ' "$RSYNC_LOG" | tail -n "${SHOW_LINES:-60}" || true
}

if [ "$DRY_RUN" = "1" ]; then
  say "DRY RUN — changes that would be made to $APP_DIR (nothing is written)"
  SHOW_LINES=100000 sync_dir "${EXCLUDES[@]}" "$REPO_DIR/" "$APP_DIR/"
  if [ -n "$PUBLIC_DIR" ]; then
    say "DRY RUN — changes to $PUBLIC_DIR"
    PUB_EX=(--exclude=/index.php --exclude=/.htaccess --exclude=.user.ini --exclude=error_log --exclude=/.well-known/ --exclude=/cgi-bin/)
    for d in "${UPLOAD_DIRS[@]}"; do PUB_EX+=("--exclude=/$d/"); done
    SHOW_LINES=100000 sync_dir "${PUB_EX[@]}" "$REPO_DIR/public/" "$PUBLIC_DIR/"
  fi
  echo; echo "Lines starting with *deleting would be removed. Nothing was changed."
  exit 0
fi

# ── 2. Backups ─────────────────────────────────────────────────────────────────
say "Backing up the database"
env_get() { grep -E "^$1=" "$APP_DIR/.env" | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"; }
DB_NAME="$(env_get DB_DATABASE)"; DB_USER="$(env_get DB_USERNAME)"; DB_PASS="$(env_get DB_PASSWORD)"
DB_HOST="$(env_get DB_HOST)"; DB_PORT="$(env_get DB_PORT)"
if command -v mysqldump >/dev/null && [ -n "$DB_NAME" ]; then
  DB_FILE="$BACKUP_DIR/db-$STAMP.sql.gz"
  MYSQL_PWD="$DB_PASS" mysqldump --single-transaction --quick --no-tablespaces \
    -h "${DB_HOST:-localhost}" -P "${DB_PORT:-3306}" -u "$DB_USER" "$DB_NAME" | gzip > "$DB_FILE"
  echo "Saved $DB_FILE ($(du -h "$DB_FILE" | cut -f1))"
else
  warn "mysqldump not available — back up the database from cPanel → phpMyAdmin → Export before continuing"
  read -r -p "Continue without a database backup? [y/N] " ok; [ "$ok" = "y" ] || die "Stopped."
fi

if [ "$BACKUP_UPLOADS" = "1" ]; then
  say "Backing up uploaded files"
  for base in "$APP_DIR/public" ${PUBLIC_DIR:+"$PUBLIC_DIR"}; do
    existing=(); for d in "${UPLOAD_DIRS[@]}"; do [ -e "$base/$d" ] && [ "$d" != storage ] && existing+=("$d"); done
    if [ ${#existing[@]} -gt 0 ]; then
      name="uploads-$(basename "$base")-$STAMP.tar.gz"
      tar -czf "$BACKUP_DIR/$name" -C "$base" "${existing[@]}"
      echo "Saved $BACKUP_DIR/$name ($(du -h "$BACKUP_DIR/$name" | cut -f1))"
    fi
  done
fi

# ── 3. Maintenance mode ────────────────────────────────────────────────────────
say "Maintenance mode on"
( cd "$APP_DIR" && "$PHP" artisan down --retry=30 ) || warn "could not enable maintenance mode (continuing)"
trap 'warn "Deploy failed — site is still in maintenance mode. Fix the error, then run: cd $APP_DIR && $PHP artisan up"' ERR

# ── 4. Copy code (uploads, .env, storage, vendor are excluded) ─────────────────
say "Copying code into $APP_DIR"
sync_dir "${EXCLUDES[@]}" "$REPO_DIR/" "$APP_DIR/"

if [ -n "$PUBLIC_DIR" ]; then
  say "Copying public assets into $PUBLIC_DIR (index.php and .htaccess kept)"
  PUB_EX=(--exclude=/index.php --exclude=/.htaccess --exclude=.user.ini --exclude=error_log --exclude=/.well-known/ --exclude=/cgi-bin/)
  for d in "${UPLOAD_DIRS[@]}"; do PUB_EX+=("--exclude=/$d/"); done
  sync_dir "${PUB_EX[@]}" "$REPO_DIR/public/" "$PUBLIC_DIR/"
fi

# folders Laravel and the upload code expect
mkdir -p "$APP_DIR"/storage/{app/public,framework/{cache/data,sessions,views},logs} "$APP_DIR/bootstrap/cache" "$APP_DIR/public/photos/requests"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/public/photos" 2>/dev/null || true

# ── 5. PHP dependencies ────────────────────────────────────────────────────────
say "Installing PHP packages (composer)"
if [ -z "$COMPOSER" ]; then
  if command -v composer >/dev/null; then COMPOSER="composer"
  else
    [ -f "$HOME/composer.phar" ] || "$PHP" -r "copy('https://getcomposer.org/installer', '$HOME/composer-setup.php');"
    [ -f "$HOME/composer.phar" ] || "$PHP" "$HOME/composer-setup.php" --quiet --install-dir="$HOME"
    rm -f "$HOME/composer-setup.php"
    COMPOSER="$PHP $HOME/composer.phar"
  fi
fi
( cd "$APP_DIR" && $COMPOSER install --no-dev --optimize-autoloader --no-interaction --no-progress $COMPOSER_FLAGS )

# ── 6. Database changes + caches ───────────────────────────────────────────────
say "Running migrations"
( cd "$APP_DIR" && "$PHP" artisan migrate --force )

say "Refreshing caches"
( cd "$APP_DIR" \
  && "$PHP" artisan optimize:clear \
  && "$PHP" artisan config:cache \
  && "$PHP" artisan view:cache )        # no route:cache — routes/web.php has closure routes
[ -e "$APP_DIR/public/storage" ] || ( cd "$APP_DIR" && "$PHP" artisan storage:link ) || true

# ── 7. Back online ─────────────────────────────────────────────────────────────
trap - ERR
( cd "$APP_DIR" && "$PHP" artisan up )

# keep only the newest $KEEP_BACKUPS deploys' backups (uploads may be 2 files per deploy)
ls -1t "$BACKUP_DIR"/db-*.sql.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
ls -1t "$BACKUP_DIR"/uploads-*.tar.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS * 2 + 1)) | xargs -r rm -f


say "Done — deployed $(git -C "$REPO_DIR" log -1 --format='%h %s')"
