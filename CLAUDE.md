# PRIAM — priamtiv.com

## What this repo is

This is the WordPress child theme (Genesis framework) for **priamtiv.com**, the marketing and product page for PRIAM — PowerData's GRC SaaS product for small businesses.

This repo was created in June 2026 when the site was migrated from Drupal (repo: `priamtiv-drupal`) to WordPress.

---

## Stack

| Layer | Technology |
|---|---|
| CMS | WordPress 6.x |
| Parent theme | Genesis 3.6.2 |
| Child theme | `powerdata-theme` (this repo) |
| Contact form spam protection | Cloudflare Turnstile |
| SMTP | Hostinger (`smtp.hostinger.com:465 SSL`) |
| Hosting | Hostinger shared hosting |
| PHP | 8.3 |
| Database | MySQL — `u891148405_nFdBt` |

---

## Hosting

- **Provider**: Hostinger
- **SSH user**: `u891148405`
- **SSH host**: shown in hPanel → SSH Access
- **Public root**: `~/domains/priamtiv.com/public_html/`
- **Theme directory**: `~/domains/priamtiv.com/public_html/wp-content/themes/powerdata-theme/`

---

## Git → Hostinger deploy

```
git push origin main
  → GitHub: PowerDataGRC/priamtiv
    → Hostinger auto-deploy (hPanel → Git)
      → public_html/wp-content/themes/powerdata-theme/
```

Only the theme directory is managed by git. WordPress core, Genesis, plugins, uploads, and `wp-config.php` are managed separately on the server.

---

## Secrets

Credentials are stored outside `public_html/` in a file that is never overwritten by deploys:

**File**: `~/priamtiv.wp-config-local.php` on the Hostinger server

```php
<?php
define( 'CF_TURNSTILE_SITE_KEY',   '...' );
define( 'CF_TURNSTILE_SECRET_KEY', '...' );
define( 'SMTP_PASSWORD',           '...' );  // hello@priamtiv.com Hostinger password
```

`wp-config.php` (in `public_html/`) includes this file at runtime.

---

## Pages

| Page | Slug | Notes |
|---|---|---|
| PRIAM Platform | `priam` | Set as static homepage (Settings → Reading) |
| Contact | `contact` | Contact form with Turnstile |
| Consulting | `consulting` | Placeholder for future use |
| Training | `training` | Placeholder for future use |
| Privacy Policy | `privacy-policy` | Placeholder |
| Terms | `terms` | Placeholder |

---

## Key files in this repo

| File | Purpose |
|---|---|
| `functions.php` | Genesis hooks, Turnstile shortcode, contact form AJAX handler, SMTP config, JSON-LD schema, custom footer |
| `style.css` | Theme declaration + complete design system (CSS custom properties, layout, components) |
| `theme.json` | Gutenberg design tokens (colors, fonts, spacing) |
| `assets/site.js` | Scroll reveal animations, mobile nav toggle, contact form AJAX submit |
| `assets/editor-style.css` | Block editor preview styles |
| `patterns/consulting-patterns.php` | Gutenberg block patterns (PowerData category) |
| `page-priam-blocks.html` | Gutenberg block HTML for the PRIAM Platform page — copy-paste into Code Editor |
| `page-contact-blocks.html` | Gutenberg block HTML for the Contact page |
| `page-*.html` | Reference HTML for other pages (future use) |
| `DEPLOYMENT.md` | One-time server setup guide (WP-CLI commands, secrets, Git deploy config) |
| `HOW-TO.md` | Ongoing maintenance guide — content editing, menu updates, SMTP/Turnstile, updates |

---

## Contact form

- Form fields: name (required), email (required), company (optional), message (required)
- Handled via WordPress AJAX (`wp-admin/admin-ajax.php`, action `pd_contact_form`)
- Spam protection: Cloudflare Turnstile (`[pd_turnstile]` shortcode)
- Email sent to: WordPress admin email (`Settings → General → Administration email address`)
- SMTP: `smtp.hostinger.com:465` SSL, username `hello@priamtiv.com`, password from secrets file
- Confirmation message: "Message sent! We will be in touch within one business day."

---

## Design system

Colors and typography are defined as CSS custom properties in `style.css` and as Gutenberg tokens in `theme.json`. The design uses:

- Display font: Schibsted Grotesk (sans-serif headings)
- Body font: Hanken Grotesk
- Accent font: Instrument Serif (italic emphasis, `.serif-em`)
- Primary accent: teal (`--accent` / `#16A276`)
- Background: off-white (`--surface` / `#F7F5F0`)
- Dark band: dark ink (`band-ink` class)

---

## Previous Drupal site

The old Drupal site code lives in `/home/max/codeRepo/priamtiv-drupal` (local) and `PowerDataGRC/priamtiv-drupal` (GitHub). It can be referenced for historical content or CSS values but is no longer deployed.
