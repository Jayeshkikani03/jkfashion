# JK Fashion — Demo host on InfinityFree (new guide)

Use this **only for a free demo / test site**.  
For real customer hosting (Hostinger + old domain), use [`HOSTING.md`](HOSTING.md) — **do not replace that guide**.

**GitHub code:** https://github.com/Jayeshkikani03/jkfashion  
**Local (dev):** http://localhost:10004  
**Live demo URL:** https://newfashion.freedev.app

### Current InfinityFree MySQL (demo)

```
DB_NAME     = if0_43460965_newfashion
DB_USER     = if0_43460965
DB_PASSWORD = (panel / account password — keep private)
DB_HOST     = sql210.infinityfree.com
WP_HOME     = https://newfashion.freedev.app
WP_SITEURL  = https://newfashion.freedev.app
```

---

## 0. What InfinityFree is good for

| Good | Not good |
|------|----------|
| Free short demo | Real shop / COD for customers |
| Show theme + products to someone | Heavy image import (may timeout) |
| Free subdomain | Fast / always-on professional site |

Expect: slower site, free ads sometimes, limits on CPU / MySQL / outbound downloads.

---

## 1. Create InfinityFree account + website

1. Open: https://www.infinityfree.com/  
2. Sign up / login  
3. **Create Account** → **Create Website** (or **Add Website**)  
4. Choose a **free subdomain**, example:
   - `jkfashion-demo.infinityfreeapp.com`  
   (exact ending may be `.infinityfreeapp.com` or similar — use what the panel shows)
5. Save and wait until the site status is **Active**

Write down:

```
DEMO_URL   = https://________________.infinityfreeapp.com
CLIENT_AREA login email / password
```

---

## 2. Create MySQL database (demo)

1. InfinityFree control panel → **MySQL Databases**  
2. **Create Database**  
3. Copy these values (names are long — copy carefully):

```
DB_NAME     = ________________
DB_USER     = ________________
DB_PASSWORD = ________________
DB_HOST     = ________________   (often sqlXXX.infinityfree.com — NOT localhost)
```

Important: on InfinityFree, `DB_HOST` is usually a special host name, **not** `localhost`.

---

## 3. Upload your code (ready ZIP — no edit after upload)

### Fastest: Upload & Unzip (recommended)

A ready demo ZIP can be built on your PC at:

`C:\Users\sspl1386\Local Sites\dynamicblog\app\jkfashion-infinityfree-demo.zip`

It already includes `wp-config.php` with your InfinityFree DB settings.

1. InfinityFree File Manager → open **`/htdocs`** (must be empty)  
2. Left side → **Upload & Unzip**  
3. Choose `jkfashion-infinityfree-demo.zip`  
4. Wait until unzip finishes  
5. Confirm you see `index.php`, `wp-config.php`, `wp-admin`, `wp-content` **directly inside htdocs**  
   (not inside an extra folder)

If files landed inside `jkfashion-infinityfree-demo/`, move everything up into `htdocs`.

### Option B — GitHub ZIP (needs wp-config after upload)

1. Download ZIP from https://github.com/Jayeshkikani03/jkfashion  
2. Upload & Unzip into `htdocs`  
3. Upload/rename your InfinityFree `wp-config.php` (see Part 4)

---

## 4. Create `wp-config.php` (edit demo DB details)

1. In `htdocs`, copy `wp-config-sample.php` → rename to `wp-config.php`  
2. Edit with InfinityFree DB values:

```php
define( 'DB_NAME', 'PASTE_DB_NAME' );
define( 'DB_USER', 'PASTE_DB_USER' );
define( 'DB_PASSWORD', 'PASTE_DB_PASSWORD' );
define( 'DB_HOST', 'PASTE_DB_HOST' );  // sqlXXX.infinityfree.com style
```

3. Add fresh salts from: https://api.wordpress.org/secret-key/1.1/salt/  
4. Save

Do **not** upload your Local `wp-config.php` (Local uses `root` / `localhost`).

Optional (helps some free hosts):

```php
define( 'WP_HOME', 'https://YOUR-SUBDOMAIN.infinityfreeapp.com' );
define( 'WP_SITEURL', 'https://YOUR-SUBDOMAIN.infinityfreeapp.com' );
```

---

## 5. Install WordPress + activate shop

1. Open your demo URL in browser  
2. If install screen shows → set language → site title `JK Fashion Demo` → create temp admin → Install  
3. Login to `/wp-admin/`  
4. **Plugins** → activate **VL Core**  
5. **Appearance → Themes** → activate **Dynamic Fashion Theme**  
6. **Settings → Permalinks** → **Post name** → Save  
7. Open Dashboard once (auto-setup may create `jayesh` + try import)

### If auto-import fails / times out (common on free hosts)

1. Go to **Import Catalog**  
2. Import **one JSON source at a time**  
3. If images fail: import **without images** first, add photos later  
4. **Site Settings** → Rebuild menus  

---

## 6. Demo settings to edit

**Settings → General**

| Field | Value |
|-------|--------|
| WordPress Address | your `https://....infinityfreeapp.com` |
| Site Address | same |

**Site Settings (VL)**

- Change hero links away from `localhost:10004`  
- Set site name / tagline for demo  
- Rebuild menus  

Login: `jayesh` (bootstrap password) — change it on demo too.

---

## 7. Demo test checklist

1. [ ] Demo URL opens  
2. [ ] Theme + VL Core active  
3. [ ] Home shows products (at least some)  
4. [ ] `/guides/` opens  
5. [ ] Mobile menu hamburger works  
6. [ ] One product page opens  
7. [ ] (Optional) test COD order  

Share this link with testers: `https://YOUR-SUBDOMAIN.infinityfreeapp.com`

---

## 8. Limits & fixes (InfinityFree)

| Problem | What to do |
|---------|------------|
| Upload too slow | Use FTP (FileZilla), not only browser upload |
| 403 / blocked | Check InfinityFree forum rules; no forbidden scripts |
| White screen | PHP version 8.x if selectable; check error log |
| Import timeout | Import smaller batches / no images first |
| DB connection error | Wrong `DB_HOST` — must match panel (not `localhost`) |
| SSL warning | Use the https URL InfinityFree gives; wait for cert |
| Site sleeps / slow | Normal on free plan — demo only |

---

## 9. After demo → real host

When demo is enough:

1. Buy **Hostinger Single** (or Premium)  
2. Link your **old domain**  
3. Follow [`HOSTING.md`](HOSTING.md) for production  
4. You can delete the InfinityFree demo later  

Code stays in GitHub — same project for demo and live.

---

## 10. One-page summary (InfinityFree demo)

1. Create InfinityFree website (free subdomain)  
2. Create MySQL DB → copy name/user/pass/**host**  
3. Upload GitHub files into `htdocs`  
4. Create `wp-config.php` with InfinityFree DB  
5. Install WP → activate **VL Core** + **Dynamic Fashion Theme**  
6. Permalinks → Post name  
7. Import products in small batches if needed  
8. Fix URLs → test → share demo link  

**This file:** `docs/DEMO-INFINITYFREE.md`  
**Production file (unchanged purpose):** `docs/HOSTING.md`
