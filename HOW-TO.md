# priamtiv.com — Site Maintenance & Workflow Guide

## How GitHub and Hostinger are connected

```
Your local machine
  └── /home/max/codeRepo/priamtiv/   (this repo)
        ↓  git push origin main
GitHub
  └── PowerDataGRC/priamtiv          (remote repo)
        ↓  Hostinger auto-deploy (fires on every push)
Hostinger server
  └── ~/domains/priamtiv.com/public_html/wp-content/themes/powerdata-theme/
```

**What auto-deploy does:**
Hostinger watches the `main` branch on GitHub. When you push, it runs `git pull` (or equivalent) into the theme directory only. WordPress core, Genesis, plugins, uploads, and `wp-config.php` are untouched.

**What auto-deploy does NOT manage:**
- WordPress core (updated via WP-CLI or the admin dashboard)
- Page content (edited via the WordPress block editor)
- The secrets file (`~/priamtiv.wp-config-local.php`)
- Media uploads (`wp-content/uploads/`)

---

## Day-to-day: editing page content

All page content lives in the WordPress database and is edited directly in the admin — no git involved.

1. Go to `https://priamtiv.com/wp-admin/edit.php?post_type=page`
2. Open the page (e.g., PRIAM Platform or Contact)
3. Edit text, links, or sections in the block editor
4. Click **Update**

The change is live immediately. No deploy needed.

**Editing raw HTML blocks:**
For sections that use `wp:html` blocks (most of the PRIAM page):
1. Click the block to select it
2. Click **⋮ → Edit as HTML** or switch to Code Editor view (`Ctrl+Shift+Alt+M`)
3. Edit the HTML directly
4. Switch back to Visual, confirm it looks right, click **Update**

---

## Day-to-day: editing the theme (CSS, JS, PHP)

Any change to theme files (style.css, functions.php, assets/site.js, etc.) goes through git.

```bash
# 1. Make your changes locally in /home/max/codeRepo/priamtiv/

# 2. Commit
git add .
git commit -m "Brief description of what changed"

# 3. Push — Hostinger auto-deploy fires automatically
git push origin main
```

After the push, allow 15–30 seconds for Hostinger to pull and apply the changes, then refresh the site.

**No need to SSH in for theme changes** — the deploy handles it.

---

## Updating the navigation menu

Menus are stored in WordPress, not in git.

1. Go to **Appearance → Menus** (`/wp-admin/nav-menus.php`)
2. Select **Primary Navigation**
3. Add, remove, or reorder items using the drag interface
4. Click **Save Menu**

Changes are live immediately.

---

## Adding or editing pages

**To add a new page:**
1. **Pages → Add New**
2. Set the title and slug
3. Paste block content if you have a reference file (e.g., a new `page-*.html`)
4. Check **Hide page title** in the Page Options sidebar (for full-bleed pages)
5. Publish

**To make a page the homepage:**
1. **Settings → Reading**
2. Set "Homepage" to the desired page
3. Save

---

## Updating contact form email destination

The form sends to the WordPress **admin email** (`Settings → General → Administration email address`). To change it:
1. **Settings → General**
2. Update **Administration email address**
3. Save (WordPress sends a confirmation to the new address — confirm it)

---

## Updating SMTP or Turnstile credentials

These live in the secrets file on the server, outside git. To update:

```bash
ssh u891148405@[HOSTINGER_SSH_HOST]
nano ~/priamtiv.wp-config-local.php
```

After editing, WordPress picks up the new values immediately — no restart or cache clear needed.

The secrets file format:
```php
<?php
define( 'CF_TURNSTILE_SITE_KEY',   'your-site-key' );
define( 'CF_TURNSTILE_SECRET_KEY', 'your-secret-key' );
define( 'SMTP_PASSWORD',           'your-smtp-password' );
```

---

## Updating the logo

1. **Appearance → Customize → Site Identity**
2. Click **Select logo**
3. Upload the new SVG or PNG
4. Recommended size: 394 × 74 px (or 2× for retina)
5. Click **Publish**

For the footer logo (dark version used on the dark background), it is referenced directly in the block HTML of each page. Search the block content for `logo-light.svg` and update the `src` attribute to the new upload URL.

---

## WordPress core and plugin updates

Check for updates monthly.

```bash
ssh u891148405@[HOSTINGER_SSH_HOST]

# Check what's available
wp core check-update --path=~/domains/priamtiv.com/public_html
wp plugin list --update=available --path=~/domains/priamtiv.com/public_html

# Apply updates
wp core update --path=~/domains/priamtiv.com/public_html
wp core update-db --path=~/domains/priamtiv.com/public_html
wp plugin update --all --path=~/domains/priamtiv.com/public_html
```

Or use **Dashboard → Updates** in the WordPress admin for a GUI approach.

---

## Clearing the WordPress cache

If you have a caching plugin (e.g., LiteSpeed Cache):
- Admin: **LiteSpeed → Purge All**
- WP-CLI: `wp cache flush --path=~/domains/priamtiv.com/public_html`

If you don't have a caching plugin, WordPress doesn't cache page HTML by default — no action needed.

---

## Reconfiguring the Hostinger Git auto-deploy

If you ever need to change which repo or branch Hostinger deploys from:

1. Log into **hPanel** → **Git**
2. Delete the existing deploy entry (if any)
3. Click **New deploy**:
   - Repository URL: `https://github.com/PowerDataGRC/priamtiv.git`
   - Branch: `main`
   - Deploy directory: `public_html/wp-content/themes/powerdata-theme`
4. Save and click **Deploy** to trigger the first pull

**Important:** The deploy directory must be the theme folder, not `public_html/`. Setting it to `public_html/` would overwrite WordPress core files.

---

## Restoring access after lockout

**Forgot admin password:**
```bash
ssh u891148405@[HOSTINGER_SSH_HOST]
wp user update admin --user_pass="NewPassword123!" \
  --path=~/domains/priamtiv.com/public_html
```

**Site shows default WordPress page (theme deactivated):**
```bash
wp theme activate powerdata-theme \
  --path=~/domains/priamtiv.com/public_html
```

**Site shows install wizard (wp-config missing):**
Check that `wp-config.php` exists in `public_html/` and that the secrets file path in it is correct.

---

## Troubleshooting: contact form not sending email

1. **Turnstile widget is missing** — Check that `CF_TURNSTILE_SITE_KEY` is in `~/priamtiv.wp-config-local.php`. Log in as admin to see the red error message if the key is absent.

2. **Form submits but no email arrives** — Test SMTP from the command line:
   ```bash
   wp eval "wp_mail('hello@priamtiv.com','Test','SMTP test from WP-CLI');" \
     --path=~/domains/priamtiv.com/public_html
   ```
   If that returns `false`, check the SMTP password in the secrets file and confirm `smtp.hostinger.com:465` is accessible.

3. **"Human verification failed" error** — The Turnstile secret key may be wrong, or the domain hasn't been added to your Cloudflare Turnstile dashboard allowed-hostnames list.

---

## Reference: what lives where

| Item | Location | How to change |
|---|---|---|
| Theme files (CSS, JS, PHP) | `priamtiv` git repo | Edit locally → git push |
| Page content (hero, sections) | WordPress database | Edit via WP admin block editor |
| Navigation menu | WordPress database | Appearance → Menus |
| Logo | WordPress media library | Appearance → Customize → Site Identity |
| Turnstile keys | `~/priamtiv.wp-config-local.php` | SSH → nano |
| SMTP password | `~/priamtiv.wp-config-local.php` | SSH → nano |
| DB credentials | `wp-config.php` in `public_html/` | SSH → nano (rarely needed) |
| Media uploads | `wp-content/uploads/` | WP admin → Media |
| WordPress core | `public_html/` | WP-CLI or WP admin |
| Genesis parent theme | `wp-content/themes/genesis/` | WP admin upload (rare) |
