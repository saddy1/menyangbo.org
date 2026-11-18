#!/usr/bin/env bash
#
# Simple deploy for menyangbo.org on cPanel.
#
#   bash deploy.sh              update code from GitHub (database is NOT touched)
#   MIGRATE=1 bash deploy.sh    also run database migrations (DB is backed up first)
#
# Never touched: .env, storage/, vendor/, uploaded files in public/, .htaccess
#
set -Eeuo pipefail

# Run from a temp copy, because step 1 updates the folder this script lives in.
if [ -z "${DEPLOY_COPY:-}" ]; then
  tmp="$(mktemp)"; cp "$0" "$tmp"; DEPLOY_COPY="$tmp" exec bash "$tmp" "$@"
fi
trap 'rm -f "$DEPLOY_COPY"' EXIT

# ── Settings ───────────────────────────────────────────────────────────────────
APP_DIR="${APP_DIR:-$HOME/menyangbo.org}"      # live site (has artisan + .env)
REPO_DIR="${REPO_DIR:-$HOME/menyangbo-repo}"    # clean copy of GitHub repo
REPO_URL="${REPO_URL:-https://github.com/saddy1/menyangbo.org.git}"
BRANCH="${BRANCH:-main}"
PHP="${PHP:-php}"
MIGRATE="${MIGRATE:-0}"                         # 1 = run migrations
BACKUP_DIR="$HOME/menyangbo-backups"

step() { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
fail() { printf '\033[1;31m!! %s\033[0m\n' "$*" >&2; exit 1; }

[ -f "$APP_DIR/artisan" ] || fail "No Laravel app in $APP_DIR (run with APP_DIR=/path/to/site)"
[ -f "$APP_DIR/.env" ]    || fail "$APP_DIR/.env is missing"
artisan() { ( cd "$APP_DIR" && "$PHP" artisan "$@" ); }

# ── 1. Get latest code ─────────────────────────────────────────────────────────
step "Getting latest $BRANCH from GitHub"
if [ -d "$REPO_DIR/.git" ]; then
  git -C "$REPO_DIR" fetch --quiet origin "$BRANCH"
  git -C "$REPO_DIR" reset --quiet --hard "origin/$BRANCH"
else
  git clone --quiet --branch "$BRANCH" "$REPO_URL" "$REPO_DIR"
fi
echo "Commit: $(git -C "$REPO_DIR" log -1 --format='%h %s (%cr)')"

# ── 2. Maintenance mode (and always come back up, even on error) ───────────────
step "Maintenance mode on"
artisan down --retry=30 || true
trap 'printf "\033[1;31m!! Deploy failed — bringing the site back up\033[0m\n"; artisan up || true; rm -f "$DEPLOY_COPY"' ERR

# ── 3. Copy code (only files tracked in git; uploads/.env/storage/vendor stay) ──
step "Copying code into $APP_DIR"
idx="$(mktemp)"; rm -f "$idx"
GIT_INDEX_FILE="$idx" git -C "$REPO_DIR" read-tree HEAD
for f in .htaccess public/.htaccess; do     # keep the server's own .htaccess
  [ -e "$APP_DIR/$f" ] && GIT_INDEX_FILE="$idx" git -C "$REPO_DIR" update-index --force-remove -- "$f"
done
GIT_INDEX_FILE="$idx" git -C "$REPO_DIR" checkout-index -a -f --prefix="$APP_DIR/"
rm -f "$idx"
mkdir -p "$APP_DIR"/storage/{app/public,framework/{cache/data,sessions,views},logs} "$APP_DIR/bootstrap/cache"

# ── 4. PHP packages ────────────────────────────────────────────────────────────
step "Installing PHP packages"
if command -v composer >/dev/null; then COMPOSER="composer"
else
  [ -f "$HOME/composer.phar" ] || ( cd "$HOME" && curl -sS https://getcomposer.org/installer | "$PHP" -- --quiet )
  COMPOSER="$PHP $HOME/composer.phar"
fi
# --no-scripts: cPanel disables proc_open, so run package:discover ourselves
( cd "$APP_DIR" && $COMPOSER install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts )
artisan package:discover

# ── 5. Database (only with MIGRATE=1) ──────────────────────────────────────────
if [ "$MIGRATE" = "1" ]; then
  step "Backing up database, then migrating"
  mkdir -p "$BACKUP_DIR"
  env_get() { grep -E "^$1=" "$APP_DIR/.env" | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
  out="$BACKUP_DIR/db-$(date +%Y%m%d-%H%M%S).sql.gz"
  MYSQL_PWD="$(env_get DB_PASSWORD)" mysqldump --single-transaction \
    -h "$(env_get DB_HOST)" -u "$(env_get DB_USERNAME)" "$(env_get DB_DATABASE)" | gzip > "$out"
  echo "Backup: $out"
  artisan migrate --force
else
  step "Database not touched (use MIGRATE=1 to run migrations)"
fi

# ── 6. Caches and back online ──────────────────────────────────────────────────
step "Refreshing caches"
artisan optimize:clear
artisan config:cache
artisan view:cache
[ -e "$APP_DIR/public/storage" ] || artisan storage:link || true

trap 'rm -f "$DEPLOY_COPY"' ERR
artisan up
step "Done — $(git -C "$REPO_DIR" log -1 --format='%h %s')"
