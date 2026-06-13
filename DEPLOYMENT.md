# Deployment Guide — priamtiv.com on Hostinger (WordPress)

## Architecture

| Layer | Path on server |
|---|---|
| WordPress root | `~/domains/priamtiv.com/public_html/` |
| Theme (git-managed) | `~/domains/priamtiv.com/public_html/wp-content/themes/powerdata-theme/` |
| Genesis parent theme | `~/domains/priamtiv.com/public_html/wp-content/themes/genesis/` |
| Secrets file | `~/priamtiv.wp-config-local.php` (outside git, never overwritten by deploy) |
| PHP version | 8.3 |
| Database | MySQL on `localhost` — `u891148405_nFdBt` |

**This git repo** (`PowerDataGRC/priamtiv`) is deployed only to the child theme directory.
WordPress core and Genesis live in `public_html/` and are managed separately (WP-CLI or admin).

---

## SSH Access

```
ssh u891148405@[HOSTINGER_SSH_HOST]
```

The hostname is shown in **hPanel → SSH Access**.

---

## One-Time Server Setup

Perform these steps once when first deploying.

### 1. SSH into Hostinger

```bash
ssh u891148405@[HOSTINGER_SSH_HOST]
cd ~/domains/priamtiv.com/public_html
```

### 2. Drop Drupal database tables and install WordPress

Hostinger has WP-CLI available. Use it to install WordPress into the existing MySQL database:

```bash
# DB name is u891148405_nFdBt — get the user and password from hPanel → Databases

wp core download --path=~/domains/priamtiv.com/public_html

wp config create \
  --dbname=u891148405_nFdBt \
  --dbuser=DB_USER \
  --dbpass=DB_PASSWORD \
  --dbhost=localhost \
  --path=~/domains/priamtiv.com/public_html \
  --skip-check \
  --extra-php <<'PHP'
// Loaded from secrets file outside public_html (survives git deploys)
if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . '/../../../priamtiv.wp-config-local.php' ) ) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/../../../priamtiv.wp-config-local.php';
}
PHP

# Drop all existing Drupal tables then install WordPress
wp db reset --yes --path=~/domains/priamtiv.com/public_html

wp core install \
  --url=https://priamtiv.com \
  --title="PRIAM" \
  --admin_user=admin \
  --admin_email=hello@priamtiv.com \
  --admin_password=CHOOSE_A_STRONG_PASSWORD \
  --path=~/domains/priamtiv.com/public_html
```

### 3. Create the secrets file

This file lives outside `public_html/` so it is never overwritten by git deploys.

```bash
cat > ~/priamtiv.wp-config-local.php << 'EOF'
<?php
define( 'CF_TURNSTILE_SITE_KEY',   'YOUR_TURNSTILE_SITE_KEY' );
define( 'CF_TURNSTILE_SECRET_KEY', 'YOUR_TURNSTILE_SECRET_KEY' );
define( 'SMTP_PASSWORD',           'YOUR_SMTP_PASSWORD' );   // hello@priamtiv.com Hostinger password
EOF

chmod 600 ~/priamtiv.wp-config-local.php
```

Replace placeholder values:

| Placeholder | Where to find it |
|---|---|
| `YOUR_TURNSTILE_SITE_KEY` | Cloudflare Turnstile dashboard |
| `YOUR_TURNSTILE_SECRET_KEY` | Cloudflare Turnstile dashboard |
| `YOUR_SMTP_PASSWORD` | hPanel → Email → Manage → hello@priamtiv.com |

### 4. Install Genesis parent theme

```bash
# Upload genesis.3.6.2.zip to the server, then:
wp theme install ~/genesis.3.6.2.zip \
  --path=~/domains/priamtiv.com/public_html
```

Or install via WordPress admin: **Appearance → Themes → Add New → Upload Theme**.

### 5. Configure Hostinger Git auto-deploy

1. In **hPanel → Git**, delete the existing deploy for the `priamtiv-drupal` repo
2. Add a new deploy:
   - **Repository URL**: `https://github.com/PowerDataGRC/priamtiv.git`
   - **Branch**: `main`
   - **Deploy directory**: `public_html/wp-content/themes/powerdata-theme`
3. Save and trigger the first deploy

After the first deploy, confirm the theme directory exists:
```bash
ls ~/domains/priamtiv.com/public_html/wp-content/themes/powerdata-theme/
```

### 6. Activate Genesis and child theme

```bash
wp theme activate powerdata-theme \
  --path=~/domains/priamtiv.com/public_html
```

Or via **Appearance → Themes** in the WordPress admin.

### 7. Upload the logo

1. Go to **Appearance → Customize → Site Identity**
2. Upload `logo.svg` (from the old Drupal theme's `images/` directory) as the logo
3. Recommended: also upload `logo-light.svg` for the footer via the block content directly
4. Upload `favicon-96x96.png` as the site icon

### 8. Configure WordPress settings

In **Settings → General**:
- Site title: `PRIAM`
- Tagline: `The simple GRC platform for small businesses`
- Admin email: `hello@priamtiv.com`

In **Settings → Reading**:
- Set "Your homepage displays" to **A static page**
- Homepage: `PRIAM Platform` (create in step 9 below)

In **Settings → Permalinks**:
- Select **Post name** (`/%postname%/`)
- Save (regenerates `.htaccess`)

### 9. Create the pages

Go to **Pages → Add New** and create these four pages:

| Page Title | Slug | Content |
|---|---|---|
| PRIAM Platform | `priam` | Paste `page-priam-blocks.html` (see step 10) |
| Contact | `contact` | Paste `page-contact-blocks.html` (see step 10) |
| Consulting | `consulting` | Leave blank (placeholder for future) |
| Training | `training` | Leave blank (placeholder for future) |
| Privacy Policy | `privacy-policy` | Paste placeholder content (see below) |
| Terms | `terms` | Paste placeholder content (see below) |

**Privacy Policy placeholder content** (paste into Code Editor):
```html
<!-- wp:html -->
<section class="pd-section-tight" style="padding-top:clamp(48px,7vw,84px);">
  <div class="pd-wrap" style="max-width:720px;margin:0 auto;">
    <h1>Privacy Policy</h1>
    <p style="color:var(--muted);margin-top:8px;">Last updated: June 2026</p>
    <p class="lede" style="margin-top:24px;">This page is under construction. Please contact us at <a href="mailto:hello@priamtiv.com">hello@priamtiv.com</a> with any privacy-related questions.</p>
  </div>
</section>
<!-- /wp:html -->
```

**Terms placeholder content** (paste into Code Editor):
```html
<!-- wp:html -->
<section class="pd-section-tight" style="padding-top:clamp(48px,7vw,84px);">
  <div class="pd-wrap" style="max-width:720px;margin:0 auto;">
    <h1>Terms of Service</h1>
    <p style="color:var(--muted);margin-top:8px;">Last updated: June 2026</p>
    <p class="lede" style="margin-top:24px;">This page is under construction. Please contact us at <a href="mailto:hello@priamtiv.com">hello@priamtiv.com</a> with any questions about our terms.</p>
  </div>
</section>
<!-- /wp:html -->
```

For the PRIAM Platform page:
- Check **Hide page title** in the Page Options sidebar box (hides the Genesis page title since the hero has its own H1)

Set **PRIAM Platform** as the static homepage in Settings → Reading.

### 10. Paste block content into pages

For PRIAM Platform and Contact pages:
1. Open the page in the block editor
2. Click **⋮ Options** → **Code editor** (or `Ctrl+Shift+Alt+M`)
3. Select all, delete, paste the content from the corresponding file
4. Switch back to Visual editor to verify layout
5. Publish

### 11. Set up navigation menu

1. Go to **Appearance → Menus**
2. Create a menu named **Primary Navigation**
3. Add these items:

| Label | Type | URL |
|---|---|---|
| Platform | Custom link | `/priam/#platform` |
| How it works | Custom link | `/priam/#how` |
| Why PRIAM | Custom link | `/priam/#why-priam` |
| FAQ | Custom link | `/priam/#faq` |
| Contact | Page | `/contact/` |

4. Add a custom link for the header CTA button:

| Label | URL | CSS class |
|---|---|---|
| Let's Talk | `/contact/` | `btn btn-primary btn-sm px-4` |

5. Assign to the **Primary** menu location
6. Save

### 12. Configure SMTP

SMTP is wired in `functions.php` (Hostinger, port 465 SSL, `hello@priamtiv.com`).
The password is loaded from `~/priamtiv.wp-config-local.php` (created in step 3).

To test email after deploy:
```bash
wp eval "wp_mail('hello@priamtiv.com', 'Test', 'SMTP working');" \
  --path=~/domains/priamtiv.com/public_html
```

### 13. Verify Turnstile

1. Visit `https://priamtiv.com/contact/`
2. The Turnstile widget should appear in the form
3. Submit the form — you should receive an email at `hello@priamtiv.com`

If the widget shows a red error message (only visible to admins), check that the keys are in `~/priamtiv.wp-config-local.php` and that the file path in `wp-config.php` is correct.

### 14. Copy images from old Drupal theme

The product screenshot images from the Drupal site should be uploaded to the WordPress media library:

```bash
# From your local machine — upload images from old theme
scp web/themes/custom/priamtiv_theme/images/admin-dashboard.png \
    web/themes/custom/priamtiv_theme/images/company-resources.png \
    web/themes/custom/priamtiv_theme/images/incident-analytics.png \
    web/themes/custom/priamtiv_theme/images/individual-incident.png \
    web/themes/custom/priamtiv_theme/images/policy-mgmt-tenant.png \
    web/themes/custom/priamtiv_theme/images/risk-assessment-results-1.png \
    u891148405@[HOSTINGER_SSH_HOST]:~/domains/priamtiv.com/public_html/wp-content/uploads/priam/
```

Or upload them via **Media → Add New** in the WordPress admin.

> Note: The PRIAM page currently uses a CSS-based dashboard mock (not a static image).
> The above images may be referenced in updated block content in the future.

---

## Regular Deployment Workflow

All changes to the theme happen in this repo. Push to GitHub → Hostinger auto-deploys to the theme directory.

### On your local machine

```bash
# 1. Edit theme files in /home/max/codeRepo/priamtiv/
# 2. Commit and push
git add .
git commit -m "Description of changes"
git push origin main
```

Hostinger's auto-deploy fires automatically on push and updates
`public_html/wp-content/themes/powerdata-theme/`.

### When to clear the WordPress cache

If your host has a caching plugin active (e.g., LiteSpeed Cache):
- Clear it after every deploy: **LiteSpeed → Purge All** in the WP admin
- Or via WP-CLI: `wp cache flush --path=~/domains/priamtiv.com/public_html`

---

## Updating page content

All page sections are standard `wp:html` blocks.

1. Open the page in the block editor
2. Switch to Code Editor view (`Ctrl+Shift+Alt+M`)
3. Find and edit the HTML directly
4. Update

The `page-*.html` files in this repo serve as reference snapshots. They are not auto-synced — edit the live page via the WordPress admin.

---

## WordPress Updates

```bash
# Check for available updates
wp core check-update --path=~/domains/priamtiv.com/public_html

# Apply core update
wp core update --path=~/domains/priamtiv.com/public_html
wp core update-db --path=~/domains/priamtiv.com/public_html

# Update all plugins
wp plugin update --all --path=~/domains/priamtiv.com/public_html
```

---

## Key Admin URLs

| Task | URL |
|---|---|
| Dashboard | `https://priamtiv.com/wp-admin` |
| Pages | `https://priamtiv.com/wp-admin/edit.php?post_type=page` |
| Menus | `https://priamtiv.com/wp-admin/nav-menus.php` |
| Customize (logo, identity) | `https://priamtiv.com/wp-admin/customize.php` |
| Themes | `https://priamtiv.com/wp-admin/themes.php` |
| Settings → Reading | `https://priamtiv.com/wp-admin/options-reading.php` |
| Settings → Permalinks | `https://priamtiv.com/wp-admin/options-permalink.php` |
| Media library | `https://priamtiv.com/wp-admin/upload.php` |

---

## Secrets reference

| Secret | Location | Format |
|---|---|---|
| Turnstile site key | `~/priamtiv.wp-config-local.php` | `define( 'CF_TURNSTILE_SITE_KEY', '...' )` |
| Turnstile secret key | `~/priamtiv.wp-config-local.php` | `define( 'CF_TURNSTILE_SECRET_KEY', '...' )` |
| SMTP password | `~/priamtiv.wp-config-local.php` | `define( 'SMTP_PASSWORD', '...' )` |
| WP DB credentials | `wp-config.php` (set during install) | standard WP config |

---

## Emergency: Lost admin access

SSH in and generate a new password or one-time login URL:

```bash
wp user update admin --user_pass=NEW_PASSWORD \
  --path=~/domains/priamtiv.com/public_html
```

---

## Page Anchors Reference

| Anchor | Page | Section |
|---|---|---|
| `#platform` | /priam/ | Five-letter PRIAM sections |
| `#how` | /priam/ | How it works |
| `#why-priam` | /priam/ | Why PRIAM grid |
| `#faq` | /priam/ | FAQ accordion |
| `/contact/` | /contact/ | Contact form |
