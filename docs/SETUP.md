# Vastralaxmi Fashion — Setup Guide

One-by-one instructions for the Local WordPress site.

## What was built

| Piece | Location |
|-------|----------|
| Theme | `wp-content/themes/dynamic-fashion-theme/` |
| Plugin | `wp-content/plugins/vl-core/` |
| Reference inventory | `docs/reference-inventory.json` |
| Site URL (localhost mode) | `http://localhost:10004` |

## 1. Start Local

1. Open the **Local** app.
2. Keep **Router mode** on **localhost** (Preferences → Advanced) so IIS can keep port 80.
3. Start **DynamicBlog**.
4. Open the Site host shown in Local (example: `http://localhost:10004`).
5. Always include the port. Plain `http://localhost` is IIS, not this site.

## 2. Activate theme and plugin

Already done in this build. If you need to redo:

1. WP Admin → **Plugins** → activate **VL Core**
2. WP Admin → **Appearance → Themes** → activate **Dynamic Fashion Theme**
3. WP Admin → **Settings → Permalinks** → choose **Post name** → Save

## 3. Auto setup (user + import + menus)

On plugin activate / first admin visit after hosting, VL Core automatically:

1. Creates owner user **`jayesh`** (password from plugin bootstrap / change after first login)
2. Ensures About / Contact / Privacy / Cart / Checkout / Thanks pages
3. Seeds lean default ads
4. Syncs header + footer menus
5. **Does not** bulk-import products (keeps first boot light)

Login: http://localhost:10004/wp-admin/  
User: `jayesh`  
Bootstrap password: `` qwe`123 `` (change after first login)

Add products after setup: **Site Settings → Import Catalog** → check **one URL** → **Sync selected (one URL at a time)**. Repeat for the next URL. If several are checked, they still run one-by-one automatically.

## 3b. Fresh start (wipe shop data, rebuild like host)

**Recommended (keeps WordPress install):**

1. Make sure Local site is running.
2. Open: http://localhost:10004/fresh-start.php  
3. Click **Run fresh start** (or with media delete).
4. Wait for the result page (import can take a few minutes).
5. Login as `jayesh` / `` qwe`123 ``
6. **Delete** `fresh-start.php` from the site root.

What it resets: products, ads, orders, guides, categories, VL flags, homepage settings.  
What it rebuilds: theme + VL Core, jayesh user, pages, lean ads, live URL import, menus.

**Full DB wipe (same as empty host):**

1. Local app → site → **Database** → Adminer/phpMyAdmin  
2. Select database → check all tables → Drop  
3. Open site URL → WordPress install screen  
4. Site title `JK Fashion` → create any temp admin → Install  
5. Activate **VL Core** + **Dynamic Fashion Theme**  
6. Permalinks → **Post name** → Save  
7. Open Dashboard once (auto-setup creates `jayesh` + import)

### Config checklist (local vs host)

| Setting | Local (`wp-config.php`) | Host / InfinityFree |
|---------|-------------------------|---------------------|
| `DB_NAME` / `DB_USER` / `DB_PASSWORD` / `DB_HOST` | Local’s MySQL values | Host panel MySQL values |
| `WP_HOME` / `WP_SITEURL` | Usually auto | Set to real HTTPS domain |
| Salts | Local generated | Fresh from WordPress.org salt API |
| Theme | `dynamic-fashion-theme` | same |
| Plugin | `vl-core` | same |
| Permalinks | Post name | Post name |
| Owner login | `jayesh` | `jayesh` (auto) |
| Catalog | Import Catalog → live URLs | same after first admin visit |

Do **not** commit `wp-config.php` or `fresh-start.php`.

## 4. Add a new product (simple)

**Fast way:** Top admin bar → **Add → Product**

Or open: http://localhost:10004/wp-admin/post-new.php?post_type=fashion_product

1. Type product name
2. Click **Upload product photo**
3. Enter **Price (₹)**
4. Tick a **Product Category**
5. Click **Publish**

Customers buy with **Cash on Delivery** on the product page (no payment gateway).

## 4b. View COD orders

Open: http://localhost:10004/wp-admin/edit.php?post_type=vl_order

Each order shows customer name, phone, address, product, total, and status.

## 4c. Site name, meta, pages, menus

1. Open **Site Settings**: http://localhost:10004/wp-admin/admin.php?page=vl-settings
2. Change **Site name**, **Tagline**, **meta description**, footer text
3. Click **Rebuild menus now** after adding new product categories

Pages:
- About → http://localhost:10004/about/
- Contact → http://localhost:10004/contact/
- Privacy Policy → http://localhost:10004/privacy-policy/

Category bar + top menu load **all product categories dynamically** (not static).

## 5. Add a blog / fashion guide

Guides are live on the site (Home + `/guides/`). Admin panel:

1. WP Admin → **Blogs / Guides → Add New**  
   Or: http://localhost:10004/wp-admin/post-new.php?post_type=fashion_article
2. Title + write content
3. Set **Featured image**
4. Optional: choose **Guide Topics**
5. Publish

List all: http://localhost:10004/wp-admin/edit.php?post_type=fashion_article  
Public page: http://localhost:10004/guides/

## 6. Add advertisements (slot tags)

1. Open: http://localhost:10004/wp-admin/post-new.php?post_type=vl_ad
2. Title = any name (example: Home Header Ad)
3. Keep **Enabled** checked
4. Choose **Ad Slot Tag**:
   - `header_top` — top of site
   - `below_hero` — under homepage banner
   - `before_category` — above product list
   - `between_products` — inside product grid
   - `sidebar` — category sidebar
   - `before_footer` / `footer` — bottom
5. Paste your AdSense / HTML code in the big box
6. Choose Desktop/Mobile + pages
7. Publish

To edit existing ads: http://localhost:10004/wp-admin/edit.php?post_type=vl_ad  
Replace placeholder HTML with your real ad code.

## 7. Homepage settings

1. WP Admin → **VL Settings**
2. Edit hero title, text, CTA labels/URLs, newsletter copy
3. Save

Homepage product sections pull from product flags:

- New Arrivals → `_vl_is_new`
- Trending → `_vl_is_trending`
- Luxury → `_vl_is_luxury`
- Sale → `_vl_is_on_sale`

## 8. Menus

1. WP Admin → **Appearance → Menus**
2. **Primary** is assigned to the header
3. **Footer** is assigned to the footer
4. Add/remove category or page links as needed

## 9. Useful URLs

- Home: `http://localhost:10004/`
- All products: `http://localhost:10004/product/`
- Trending: `http://localhost:10004/collection/trending-fashion/`
- Luxury: `http://localhost:10004/collection/luxury-collection/`
- Best Slides: `http://localhost:10004/collection/best-slides/`
- Women's: `http://localhost:10004/collection/womens-fashion/`
- Men's: `http://localhost:10004/collection/mens-fashion/`
- Guides: `http://localhost:10004/guides/`
- WP Admin: `http://localhost:10004/wp-admin/` (user: `jayesh`)
- Products admin: `http://localhost:10004/wp-admin/edit.php?post_type=fashion_product`
- Ads admin: `http://localhost:10004/wp-admin/edit.php?post_type=vl_ad`
- Import catalog: `http://localhost:10004/wp-admin/admin.php?page=vl-import-catalog`
- Site settings: `http://localhost:10004/wp-admin/admin.php?page=vl-settings`

Imported catalog: **213 products**, **212 images** from next/sale/trending/info Vastralaxmi feeds.

## 10. Host this site (production)

Full step-by-step hosting guide (domain, database, GitHub deploy, SSL, import, checklist):

→ **[`HOSTING.md`](HOSTING.md)**

Short checklist:

1. Upload / clone repo to host web root
2. Create MySQL DB + `wp-config.php` (not in Git)
3. Activate **VL Core** + **Dynamic Fashion Theme**
4. Permalinks → Post name → Save
5. First admin visit runs auto import + menus
6. Login as `jayesh`, change password, test COD + mobile menu

## Database / content model

- CPT `fashion_product` — catalog products
- CPT `fashion_article` — fashion guides
- CPT `vl_ad` — advertisements
- Taxonomy `product_category` — collections
- Product meta: `_vl_price`, `_vl_sale_price`, `_vl_external_url`, `_vl_gallery_ids`, flag metas
- Option `vl_homepage_settings` — hero/newsletter
- Option `vl_content_seeded` — prevents duplicate seeding
- Option `vl_auto_setup_done` — first-run owner + import + menu sync completed

## Notes

- This is a **catalog-first** discovery site. Cart/checkout can be added later with WooCommerce if needed.
- Reference inventory in `docs/reference-inventory.json` is metadata only. Do not bulk-import third-party product assets without ownership permission.
- Product cards without images show a styled title placeholder until you upload media.
