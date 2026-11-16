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
2. Ensures About / Contact / Privacy pages
3. Imports catalog from `docs/imports/*.json` (or remote JSON URLs if local files are missing)
4. Syncs header + footer menus from categories and pages
5. Disables unused placeholder ads

Login: http://localhost:10004/wp-admin/  
User: `jayesh`

Manual re-import anytime: **Import Catalog** → select sources → Import.

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

## 5. Add a fashion guide

1. WP Admin → **Fashion Guides → Add New**
2. Write the editorial content
3. Add a featured image
4. Publish

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

## 10. Deployment checklist

1. Copy `wp-content/themes/dynamic-fashion-theme`, `wp-content/plugins/vl-core`, and `docs/imports/*.json` to the server
2. Activate **VL Core** + **Dynamic Fashion Theme**
3. Open WP Admin once — auto-setup creates `jayesh`, imports products, syncs menus
4. Login as `jayesh` (use the bootstrap password you set, then change it)
5. Change the password after first login on production
6. Add real ad codes only when needed (Advertisements)
7. Update homepage CTAs to production URLs
8. Test mobile menu + COD order flow

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
