# Domain Bidder

Multi-domain bidding platform built with **Laravel 13**, **Inertia 3 / Vue 3**, **Filament 5**, and **Resend**. Selling domains (e.g. `agentic.io`, `onlinescrums.com`) resolve by hostname and present an offer flow with email verification.

| Layer | Local (DDEV) | Production (VPS) |
| --- | --- | --- |
| Runtime | DDEV (nginx-fpm, PHP 8.4) | Custom Docker image (PHP-FPM + Nginx + Supervisor) |
| Database | PostgreSQL 15 | MariaDB 10.11 (dedicated container) |
| Frontend | Vite HMR (`npm run dev`) | Assets compiled in image build |
| Secrets | Project `.env` | Host `.env` next to compose (not in `src/`) |

Production Docker files (`Dockerfile`, `docker-compose.yml`, `.dockerignore`) live **on the VPS only** under `~/apps/domain-bidder`. They are not part of this DDEV project.

---

## Local development (DDEV)

### Prerequisites

- [DDEV](https://ddev.readthedocs.io/)
- Docker (OrbStack, Docker Desktop, etc.)

### First-time setup

```bash
ddev start
ddev composer install
cp .env.example .env   # if needed; DDEV often manages DB_* for you
ddev artisan key:generate
ddev artisan migrate --seed
ddev npm install
```

Or use the Composer setup script where appropriate:

```bash
ddev composer setup
```

### Daily workflow

```bash
ddev start
ddev composer run dev:ddev   # queue + pail + Vite
# or: ddev npm run dev
```

### Hostnames

Configured in `.ddev/config.yaml`:

| URL | Purpose |
| --- | --- |
| `https://domain-bidder.ddev.site` | Primary project URL |
| `https://agentic.io.ddev.site` | Selling domain (maps to `agentic.io`) |
| `https://onlinescrums.com.ddev.site` | Selling domain (maps to `onlinescrums.com`) |
| `https://teamtidings.com.ddev.site` | Selling domain (maps to `teamtidings.com`) |

Vite is exposed on port **5173** (see `web_extra_exposed_ports` in DDEV config). If assets 502 / CORS fail, ensure Vite is running and you hard-refresh the selling-domain URL.

### Useful commands

```bash
ddev artisan test --compact
ddev artisan migrate
ddev artisan db:seed                # domains + legacy agentic.io offers
ddev artisan filament:user          # create an admin user if needed
vendor/bin/pint --dirty             # from inside ddev: ddev exec vendor/bin/pint --dirty
```

Legacy bids only (idempotent; requires `agentic.io` domain):

```bash
ddev artisan db:seed --class=LegacyAgenticOffersSeeder
# production:
docker exec -it domain-bidder-app php artisan db:seed --class=LegacyAgenticOffersSeeder --force
```

### Local env notes

- Quote values with spaces: `APP_NAME="Domain Bidder"` (unquoted breaks dotenv).
- Prefer `CACHE_STORE` (not `CACHE_DRIVER`).
- Mail: `MAIL_MAILER=resend` + `RESEND_API_KEY` for real sends; leave key empty in local if you are not testing mail.
- Default `.env.example` uses sqlite; DDEV typically overrides to PostgreSQL via its env injection.

---

## Production (VPS / Docker Compose)

Production matches the rest of the container-first VPS layout (like Mautic): ops files and bind-mounted data at the app root, application source in a subfolder.

### Layout

```
~/apps/domain-bidder/
  docker-compose.yml    # VPS ops (not in git)
  Dockerfile            # VPS ops (not in git)
  .dockerignore
  .env                  # production secrets (host root — NOT inside src/)
  data/                 # → container /var/www/html/storage
  db_data/              # → MariaDB /var/lib/mysql
  src/                  # git clone of this repository
```

**Composer/Node are not installed on the host.** Cloning `src/` only provides Docker build context. `composer install` and `npm run build` run inside the image build.

### Why not Alpine?

Use **Debian bookworm** (`php:8.4-fpm-bookworm`) + **Node 22**:

- Alpine needs `libzip-dev` (not just `libzip`) to compile the `zip` extension; easy to get wrong.
- Vite native packages (`lightningcss`, `@tailwindcss/oxide`) ship `*-linux-x64-gnu` (glibc), not musl.

### First deploy (summary)

1. Create dirs and clone:

   ```bash
   mkdir -p ~/apps/domain-bidder/{data,db_data}
   cd ~/apps/domain-bidder
   git clone <repo-url> src
   ```

2. Add VPS-only `Dockerfile`, `docker-compose.yml`, `.dockerignore`, and `.env` (see vault deploy guide for full templates).

3. Compose must:

   - Build with `context: .` and `COPY src/...` in the Dockerfile.
   - Bind-mount `./data` → storage, `./db_data` → MySQL data.
   - Bind-mount `./.env` → `/var/www/html/.env` (required for `artisan key:generate`).
   - Use `env_file: .env` as well.
   - Join external network `proxy-network`.
   - Label DB with `com.backup.path` for host backups.

4. Boot and initialize:

   ```bash
   docker network create proxy-network 2>/dev/null || true
   docker compose up -d --build

   # ./data starts empty and hides image storage — create dirs BEFORE artisan
   # Use plain paths (no bash brace expansion): container shell is sh/dash
   docker exec -it domain-bidder-app sh -c \
     'mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache'

   docker exec -it domain-bidder-app php artisan key:generate --force
   docker exec -it domain-bidder-app php artisan migrate --force
   docker exec -it domain-bidder-app php artisan optimize
   ```

5. Point **Nginx Proxy Manager** at container `domain-bidder-app` port **80**, with Let’s Encrypt.

### Updating code

```bash
cd ~/apps/domain-bidder/src && git pull
cd ~/apps/domain-bidder && docker compose up -d --build
```

### Production `.env` checklist

| Key | Notes |
| --- | --- |
| `APP_NAME` | Must be quoted: `"Domain Bidder"` |
| `APP_URL` | Must be `https://…` (not `http://`) when behind Cloudflare / NPM |
| `APP_KEY` | Generate via artisan after `.env` is mounted |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` |
| `DB_CONNECTION` | `mysql` (not DDEV’s postgres) |
| `DB_HOST` | `domain-bidder-db` for Option A |
| `CACHE_STORE` | Use this — `CACHE_DRIVER` is ignored on modern Laravel |
| `SESSION_DRIVER` / `QUEUE_CONNECTION` | `file` / `sync` is simplest for first bring-up |
| `MAIL_MAILER` | `resend` + `RESEND_API_KEY` |
| `BID_NOTIFICATION_EMAIL` | Ops inbox for bid alerts |

Keep `.env` at **`~/apps/domain-bidder/.env`**, not under `src/`, so re-clones and `git pull` never wipe secrets.

---

## Lessons learned (pitfalls)

| Symptom | Cause | Fix |
| --- | --- | --- |
| `Package 'libzip' not found` during `docker-php-ext-install zip` | Runtime `libzip` without `libzip-dev` | Install `*-dev` packages to compile, then purge them; prefer Debian over Alpine |
| `Encountered unexpected whitespace at [Domain Bidder]` | Unquoted `APP_NAME=Domain Bidder` | Quote: `APP_NAME="Domain Bidder"` |
| `Composer could not find a composer.json` | Build context empty / wrong folder | Clone into `src/`; Dockerfile `COPY src/composer.json` |
| `Please provide a valid cache path` (build) | Missing `storage/framework/*` in image | `mkdir` those paths in the Dockerfile before artisan |
| `Please provide a valid cache path` (runtime) | Empty `./data` bind mount over `storage` | Create framework dirs in the container after first boot |
| Brace expansion created `{cache` folder | `sh` does not support `{a,b}` | Expand paths explicitly in `sh -c` |
| `file_get_contents(.env): No such file` on `key:generate` | Compose `env_file` injects env but does not mount a file | Volume-mount `./.env:/var/www/html/.env`, or use `key:generate --show` and paste into host `.env` |
| Named Docker volumes vs Mautic layout | Volumes hide data under `/var/lib/docker` | Use host bind mounts `./data` and `./db_data` for backup scripts |
| Filament login broken / `livewire.min.js` 404 | Nginx static `.js` location serves missing file as 404; Livewire JS is a Laravel route | Add `try_files $uri /index.php?$query_string;` to the static-assets location |
| Livewire `update` to `http://…` (provisional headers) | App behind Cloudflare/NPM sees HTTP only; generates insecure Livewire URLs | Set `APP_URL=https://…`, trust proxies, `URL::forceScheme('https')` in production |
| Cloudflare 502 / nginx FATAL `invalid number of arguments in "try_files"` | Dockerfile `COPY <<EOF` expanded `$uri` at build time, mangling nginx config | Use `COPY <<"EOF"` (quoted) so nginx vars stay literal |
| Filament login Livewire `update` 404 modal | `ResolveDomain` treated `/livewire-{hash}/update` as a selling-domain page | Skip `livewire-*` / `filament/` paths in `ResolveDomain` |
| `/admin` 403 after login | Filament 5 blocks panel access in non-local envs unless `User` implements `FilamentUser` | Implement `canAccessPanel()` on `User` |

### `env_file` vs bind-mounted `.env`

- `env_file:` → variables in the container process environment (enough for HTTP).
- Bind mount `./.env` → file on disk at `/var/www/html/.env` (needed for artisan commands that read/write `.env`).

Use both.

### Mautic vs this app

| | Mautic | Domain Bidder |
| --- | --- | --- |
| Image | Pre-built from a registry | Built from `src/` on the server |
| Host folder | compose + `data/` + `db_data/` | Same, **plus** `src/` + Dockerfile |
| Host Composer/Node | Not needed | Not needed (runs in build only) |

---

## Stack reference

- PHP 8.4, Laravel 13, Fortify, Wayfinder
- Inertia 3 + Vue 3 + Tailwind 4
- Filament 5 (admin)
- Resend (transactional email)
- Pest 5 (tests)

Full production templates live in the infrastructure vault deploy note for this app; keep secrets out of git.
