# JK Fashion — Host this site (one by one)

Follow this file **from top to bottom**. Do not skip steps.

**GitHub:** https://github.com/Jayeshkikani03/jkfashion  
**Local (dev):** http://localhost:10004

---

## PART A — Before hosting (on your PC)

### A1. What must be in GitHub (code)

These are already in the project and should be on GitHub:

| Path | What it is |
|------|------------|
| `wp-content/themes/dynamic-fashion-theme/` | Storefront theme (design + mobile menu) |
| `wp-content/plugins/vl-core/` | Products, COD orders, import, menus, auto-setup |
| `docs/imports/*.json` | Product data for import |
| `docs/HOSTING.md` | This guide |
| WordPress core (`wp-admin`, `wp-includes`, etc.) | Full WP so host install is easier |

### A2. What must NOT go to GitHub

| Path | Why |
|------|-----|
| `wp-config.php` | Has database password |
| `wp-content/uploads/` | Large images (created on server / during import) |
| `.env`, passwords, tokens | Secrets |

These are blocked by `.gitignore` — good. Do not force-add them.

### A3. Push latest code to GitHub

Full token + push steps: **[`GIT-PUSH.md`](GIT-PUSH.md)**

Short version (in the site `public` folder):

```powershell
git add -A
git status
git commit -m "Update hosting docs and site code"
git -c credential.helper= push "https://x-access-token:YOUR_TOKEN@github.com/Jayeshkikani03/jkfashion.git" main
```

Token must be for **Jayeshkikani03** with **Contents: Read and write** on `jkfashion`.  
Check: https://github.com/Jayeshkikani03/jkfashion — latest commit visible.

### A4. Details you will edit later (prepare now)

Write these down before you open the host panel:

| Detail | Example | Where you will set it |
|--------|---------|------------------------|
| Domain | `jkfashion.com` | Hosting + DNS |
| Site title | `JK Fashion` | WP Settings / Site Settings |
| DB name | `jkfashion_wp` | Host MySQL + `wp-config.php` |
| DB user | `jkfashion_user` | Host MySQL + `wp-config.php` |
| DB password | (strong password) | Host MySQL + `wp-config.php` |
| DB host | usually `localhost` | `wp-config.php` |
| Admin login | `jayesh` | Auto-created by plugin |
| Contact / WhatsApp | your number | Site Settings after go-live |

---

## PART B — Buy host + domain

### B1. Buy hosting

Choose any host with:

- PHP **8.1+**
- MySQL / MariaDB
- SSL (HTTPS)
- cPanel or File Manager + PHPMyAdmin (easiest)

### B2. Point domain to host

1. In domain DNS, set **A record** to host IP (host shows this).
2. Wait until `https://yourdomain.com` reaches the host (can take minutes to hours).
3. Turn on **Free SSL** in host panel.

---

## PART C — Create database (host panel)

### C1. Create DB

cPanel → **MySQL Databases** (names differ by host):

1. Create database → copy name  
2. Create user + strong password → copy both  
3. Add user to database → **All Privileges**

### C2. Keep this note

```
DB_NAME      = ________________
DB_USER      = ________________
DB_PASSWORD  = ________________
DB_HOST      = localhost
DOMAIN       = https://________________
```

---

## PART D — Upload code to host

### D1. Put files in web root

Web root is usually `public_html` (or `www`).

**Way 1 — GitHub ZIP (easy)**

1. Open https://github.com/Jayeshkikani03/jkfashion  
2. Code → **Download ZIP**  
3. cPanel → File Manager → `public_html` → Upload → Extract  
4. If files are inside a folder like `jkfashion-main`, move **everything inside** up into `public_html`  
5. You must see `index.php`, `wp-content`, `wp-admin` directly in `public_html`

**Way 2 — Git clone (if host has Terminal/SSH)**

```bash
cd public_html
git clone https://github.com/Jayeshkikani03/jkfashion.git .
```

### D2. Confirm important folders exist on server

```
public_html/
  wp-admin/
  wp-content/
    plugins/vl-core/
    themes/dynamic-fashion-theme/
  docs/imports/
  wp-config-sample.php
  index.php
```

---

## PART E — Create `wp-config.php` (edit details)

### E1. Copy sample file

In File Manager:

1. Copy `wp-config-sample.php`  
2. Rename copy to `wp-config.php`

### E2. Edit these lines only (your real DB values)

```php
define( 'DB_NAME', 'PASTE_DB_NAME' );
define( 'DB_USER', 'PASTE_DB_USER' );
define( 'DB_PASSWORD', 'PASTE_DB_PASSWORD' );
define( 'DB_HOST', 'localhost' );
```

### E3. Add new security keys

1. Open: https://api.wordpress.org/secret-key/1.1/salt/  
2. Copy the block  
3. Replace the old `AUTH_KEY` … `NONCE_SALT` lines in `wp-config.php`

### E4. Save file

Do **not** upload your Local `wp-config.php` (it has Local passwords).

---

## PART F — Install / open WordPress

### F1. Open the site

Visit: `https://yourdomain.com`

### F2. If WordPress install screen appears

1. Language → Continue  
2. Site title: `JK Fashion` (or your name)  
3. Create a temporary admin (any user)  
4. Finish install → Log in  

### F3. If site already loads / asks login

Skip install — go to Part G.

---

## PART G — Activate shop (one by one)

### G1. Activate plugin

**Plugins** → activate **VL Core**

### G2. Activate theme

**Appearance → Themes** → activate **Dynamic Fashion Theme**

### G3. Fix links (important)

**Settings → Permalinks** → choose **Post name** → **Save**

### G4. Wait for auto-setup

Open **Dashboard** once. Plugin will:

1. Create user `jayesh`  
2. Create About / Contact / Privacy pages  
3. Import products from `docs/imports/*.json`  
4. Sync menus  

Import can take several minutes (images). Keep the tab open.

### G5. Login as shop owner

1. Logout temporary admin (optional)  
2. Login: user **`jayesh`** + bootstrap password  
3. **Users → Profile** → change password now  

---

## PART H — Edit live site details (must do)

Do these in WP Admin after setup:

### H1. General URLs

**Settings → General**

| Field | Set to |
|-------|--------|
| WordPress Address (URL) | `https://yourdomain.com` |
| Site Address (URL) | `https://yourdomain.com` |
| Site Title | JK Fashion (or brand) |
| Tagline | your short line |

Save.

### H2. Site Settings (plugin)

Open: `/wp-admin/admin.php?page=vl-settings`

Edit:

- Site name / tagline / meta description  
- Top bar text  
- Hero title + buttons (use **https://yourdomain.com/...** links, not localhost)  
- Footer text  
- WhatsApp / contact number  
- Save  

Then open **Menus** tab → **Rebuild menus now**

### H3. Re-import if products/images missing

Open: `/wp-admin/admin.php?page=vl-import-catalog`

1. Select JSON sources  
2. Keep download images ON  
3. Import  
4. Rebuild menus again  

### H4. Optional — ads

**Advertisements** → add real AdSense/HTML only when ready.  
Do not leave empty placeholder ads enabled.

---

## PART I — Test (before sharing domain)

Test each item:

1. [ ] Home opens on HTTPS  
2. [ ] Mobile: hamburger opens/closes menu  
3. [ ] Open `/collection/fashion/`  
4. [ ] Open `/collection/luxury-collection/`  
5. [ ] Open `/collection/best-slides/`  
6. [ ] Open `/collection/trending-fashion/`  
7. [ ] Open one product → place test **COD** order  
8. [ ] Order shows in **Orders (COD)**  
9. [ ] About / Contact / Privacy open  
10. [ ] No `localhost:10004` links anywhere  

---

## PART J — Later updates (after first host)

When you change code on PC:

```bash
git add -A
git commit -m "Describe change"
git push origin main
```

On server:

- **Git host:** `git pull origin main` in `public_html`  
- **No Git:** upload only changed folders:
  - `wp-content/themes/dynamic-fashion-theme/`
  - `wp-content/plugins/vl-core/`
  - `docs/imports/` (if product JSON changed)

Never overwrite on server:

- `wp-config.php`
- `wp-content/uploads/`

---

## Quick map — source category → your pages

| Source | Your page |
|--------|-----------|
| next `fashion_26` | `/collection/fashion/` |
| sale `Luxury_Cloth` | `/collection/luxury-collection/` |
| trending `Best_Slides` | `/collection/best-slides/` |
| info `Trending_Fashion` | `/collection/trending-fashion/` |

---

## If something breaks

| Problem | Do this |
|---------|---------|
| 404 on products | Permalinks → Post name → Save |
| White page | Ask host: PHP 8.1+, check error log |
| No products | Import Catalog again |
| No images | Import with images ON; ask host if outbound HTTP is allowed |
| Wrong domain / old URL | Settings → General → both URLs = `https://yourdomain.com` |
| Cannot login | Use host “reset WP password” or phpMyAdmin user table |

---

## One-page summary

1. Push code to GitHub  
2. Buy host + domain + SSL  
3. Create MySQL database  
4. Upload / clone repo into `public_html`  
5. Create `wp-config.php` with DB details + new salts  
6. Install WP if asked  
7. Activate **VL Core** + **Dynamic Fashion Theme**  
8. Permalinks → Post name  
9. Auto-setup imports products + menus  
10. Login `jayesh` → change password  
11. Edit Site Settings (remove localhost URLs)  
12. Test mobile + COD  

You only need this file: **`docs/HOSTING.md`**.
