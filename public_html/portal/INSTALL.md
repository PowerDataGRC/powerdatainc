# PowerData Client Portal — Installation Guide

## What's in this package

```
pd-portal/
├── includes/
│   ├── config.php          ← Bootstrap + constants
│   ├── db.php              ← SQLite schema + all queries
│   ├── auth.php            ← Sessions, login, magic links
│   ├── files.php           ← Upload, serve, expiry helpers
│   └── mailer.php          ← Email templates (magic link + expiry warning)
├── public/                 ← portal.powerdatainc.com root
│   ├── login.php           ← Client login (password + magic link)
│   ├── magic.php           ← Magic link handler
│   ├── files.php           ← Client file browser + upload
│   └── logout.php
├── admin/                  ← admin.powerdatainc.com root
│   ├── index.php           ← Dashboard
│   ├── clients.php         ← Create/edit clients
│   ├── files.php           ← Upload files, view all, download, remove
│   ├── settings.php        ← Turnstile keys, storage info, cron status
│   └── partials/
│       ├── admin-head.php  ← Sidebar layout header
│       └── admin-foot.php
├── assets/css/
│   └── portal.css          ← Shared design system styles
├── pd-portal-cron.php      ← WP-Cron job (drop into theme)
└── portal-htaccess.txt     ← .htaccess template
```

**Storage** (created automatically on first load):
```
wp-content/pd-portal-files/          ← Protected, outside web root
    portal.db                         ← SQLite database
    client_1/
        a3f9...abc_report.pdf
    client_2/
        ...
```

---

## Step 1 — Copy portal files to WordPress

```bash
# From your WordPress root:
cp -r pd-portal/   wp-content/pd-portal/
```

The portal files live in `wp-content/pd-portal/` — **not** in `wp-content/plugins/` or themes. They're loaded directly by the subdomain entry points.

---

## Step 2 — Create subdomain folders

In your WordPress root (or wherever your host maps subdomains):

```
/portal/        ← maps to portal.powerdatainc.com
/portal/admin/  ← maps to admin.powerdatainc.com
```

Copy the public entry-point files:
```bash
# Portal client files (portal.powerdatainc.com)
cp wp-content/pd-portal/public/*.php    portal/

# Admin files (admin.powerdatainc.com)
cp -r wp-content/pd-portal/admin/       portal/admin/
cp wp-content/pd-portal/assets/         portal/assets/
```

Or use symlinks if your host supports it:
```bash
ln -s /var/www/powerdatainc.com/wp-content/pd-portal/public  /var/www/portal.powerdatainc.com
```

---

## Step 3 — Adjust wp-load.php path in entry points

Each public-facing PHP file has this line near the top:
```php
require_once __DIR__ . '/../wp-load.php';
```

Adjust the relative path to point to your WordPress root. For example if your subdomain folder is at the same level as WordPress:
```php
require_once __DIR__ . '/../../wp-load.php';
```

Test this path by checking that `WP_CONTENT_DIR` resolves correctly.

---

## Step 4 — Set up subdomains in cPanel / DNS

### DNS (Cloudflare or your registrar)
Add two CNAME (or A) records:
```
portal.powerdatainc.com  →  CNAME  powerdatainc.com
admin.powerdatainc.com   →  CNAME  powerdatainc.com
```

### Apache VirtualHost (if self-managed)
```apache
<VirtualHost *:443>
  ServerName portal.powerdatainc.com
  DocumentRoot /var/www/powerdatainc.com/portal
  SSLEngine on
  # ... your SSL cert config
</VirtualHost>

<VirtualHost *:443>
  ServerName admin.powerdatainc.com
  DocumentRoot /var/www/powerdatainc.com/portal/admin
  SSLEngine on
  # ... your SSL cert config
</VirtualHost>
```

### Nginx
```nginx
server {
  server_name portal.powerdatainc.com;
  root /var/www/powerdatainc.com/portal;
  index login.php;
  location ~ \.php$ { fastcgi_pass unix:/run/php/php8.2-fpm.sock; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
}

server {
  server_name admin.powerdatainc.com;
  root /var/www/powerdatainc.com/portal/admin;
  index index.php;
  location ~ \.php$ { fastcgi_pass unix:/run/php/php8.2-fpm.sock; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
}
```

---

## Step 5 — Copy and activate the .htaccess

```bash
cp wp-content/pd-portal/portal-htaccess.txt portal/.htaccess
# Edit portal/.htaccess to adjust paths if needed
```

Key things it does:
- Forces HTTPS
- Sets CSP, HSTS, and other security headers
- Blocks direct access to `includes/`
- Sets PHP upload limits (12 MB)

---

## Step 6 — Add the cron job to your theme

In your PowerData theme's `functions.php`, add:
```php
require_once get_stylesheet_directory() . '/pd-portal-cron.php';
```

Then copy the cron file:
```bash
cp wp-content/pd-portal/pd-portal-cron.php wp-content/themes/powerdata-theme/pd-portal-cron.php
```

Verify it's registered: **WordPress Admin → Tools → Scheduled Events** (or use WP Crontrol plugin).

---

## Step 7 — Protect the storage directory

The storage directory is created automatically at:
```
wp-content/pd-portal-files/
```

A `.htaccess` file with `Deny from all` is written there on first use. Double-check it exists:
```bash
cat wp-content/pd-portal-files/.htaccess
# Should output: Deny from all
```

On Nginx, add this to your server block:
```nginx
location ~ /pd-portal-files/ { deny all; return 403; }
```

---

## Step 8 — First login and setup

1. Visit `https://admin.powerdatainc.com`
2. You'll be redirected to **WordPress login** — sign in as an Administrator
3. You're now in the admin portal dashboard
4. Go to **Clients → New client** to add your first client
5. Go to **Files → Upload file** to add a file to that client's folder
6. Test the client portal at `https://portal.powerdatainc.com`

---

## How file expiry works

| Event | What happens |
|-------|-------------|
| File uploaded | `expires_at` set to `now + retention_days` |
| Daily cron runs | Files where `expires_at ≤ now` are marked `expired = 1` |
| Client portal | Only shows files where `expired = 0` |
| Admin portal | Shows all files (expired labeled in gray) |
| 3 days before expiry | Client receives email warning |
| "Remove" in admin | Sets `deleted = 1` — file stays on disk, hidden everywhere |

Files are **never permanently deleted** by the system. To permanently delete, SSH into the server and remove the file from `wp-content/pd-portal-files/client_N/`.

---

## Security model

| Threat | Mitigation |
|--------|-----------|
| Direct file URL guessing | Files stored with 32-char random hex prefix; behind `Deny from all` |
| Session hijacking | `HttpOnly`, `Secure`, `SameSite=Strict` cookies; server-side session invalidation on logout |
| CSRF | WordPress nonces on every form |
| Magic link reuse | Single-use token; 30-minute expiry; old tokens invalidated on new request |
| Brute force login | WordPress rate limiting + Cloudflare Turnstile on contact forms |
| Admin access | Requires WordPress Administrator role (existing WP auth) |
| File type spoofing | Extension + `finfo` MIME type double-check on upload |
| SQL injection | All queries use PDO prepared statements |
| XSS | All output escaped with `esc_html()`, `esc_attr()`, `esc_url()` |

---

## Troubleshooting

**"Forbidden" or blank page on portal:**
- Check the `wp-load.php` path in `login.php` and `files.php`
- Enable WP debug: `define('WP_DEBUG', true); define('WP_DEBUG_LOG', true);` in `wp-config.php`

**Files not uploading:**
- Check `upload_max_filesize` and `post_max_size` in `.htaccess` or `php.ini`
- Check that `wp-content/pd-portal-files/` is writable: `chmod 755 wp-content/pd-portal-files/`

**Emails not sending:**
- Install **WP Mail SMTP** and configure SMTP relay (SendGrid, Mailgun, etc.)
- Test with: WP Mail SMTP → Email Test tab

**Cron not running:**
- Some hosts disable WP-Cron. Add to `wp-config.php`: `define('DISABLE_WP_CRON', false);`
- Or set up a real server cron: `*/30 * * * * curl -s https://powerdatainc.com/wp-cron.php?doing_wp_cron > /dev/null`

---

## Required PHP extensions

- `pdo_sqlite` (SQLite database)
- `fileinfo` (MIME type validation)
- `openssl` (random_bytes, session security)

Check with: `php -m | grep -E 'pdo_sqlite|fileinfo|openssl'`
