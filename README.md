# BuyMark — Android/Mobile app + PHP REST API

This package contains **both** deliverables you asked for:

- **A. Live testable mobile app** — Expo/React Native, in `/app/frontend` (already
  running on the preview URL). It is a real, installable native app (not a WebView).
- **B. Production PHP REST API** — in `/app/php_api`, ready to drop into your EXISTING
  BuyMark project and run on your MySQL. It mirrors the exact JSON contract the app
  already speaks, so switching from preview to production is a **one-line URL change**.

Architecture (production): **Android/Expo app → PHP REST API → your MySQL (`buymark`)**.
Your website, admin panel, existing users (bcrypt), products, orders and Razorpay stay intact.

---

## 1. What changed in your database (safe, additive only)

Run `/app/php_api/migration.sql` once. It only ADDs:

| Table | Added columns | Why |
|-------|---------------|-----|
| `products` | `shop_id`, `deleted_at` | Link each product to its shop (the missing core link) + soft delete |
| `shops` | `shop_logo`, `opening_time`, `closing_time`, `services`, `offers` | Richer shop profile for the app |
| `user_orders` | `shop_id` | So a shopkeeper sees only their shop's orders |

No table is dropped, no row is deleted. Legacy products with no shop are attached to
your first shop so they stay visible (edit step 4 of the SQL if you prefer otherwise).

---

## 2. Deploy the PHP API (XAMPP / shared hosting)

1. Copy the `php_api/` folder into your BuyMark web root, e.g. `htdocs/buymark68/api/`.
2. `cp php_api/.env.example php_api/.env` and fill DB creds, a strong `JWT_SECRET`
   (`php -r "echo bin2hex(random_bytes(32));"`), and `APP_BASE_URL` (your host).
3. Import the migration: `mysql -u root buymark < php_api/migration.sql`
   (or run it in phpMyAdmin).
4. Ensure product/shop images are reachable under `APP_BASE_URL/uploads/...`
   (the API returns `APP_BASE_URL/uploads/<filename>`). Move your existing
   `assets/img` / `admin_area/product_images` into an `uploads/` folder, or adjust
   `image_url()` in `php_api/config/helpers.php` to your real image path.
5. Apache must have `mod_rewrite` on and `AllowOverride All` so `.htaccess` routes
   `/api/*` to `index.php`. Test: `GET http://<host>/buymark68/api/` → `{"success":true,...}`.

**Existing website is untouched** — the API lives in its own `api/` folder. Your PHP
session login keeps working for the website; the app uses JWT. Both share the same
`user_table` and `user_id`, so it's one account for web + app + shopkeeper.

---

## 3. Point the app at your server

In `/app/frontend/.env` set:

```
EXPO_PUBLIC_BACKEND_URL=https://your-domain-or-ngrok
```

The app calls `EXPO_PUBLIC_BACKEND_URL + /api/...`. No other change needed — the PHP
API and the preview API return identical JSON. Rebuild/restart Expo.

---

## 4. API reference (all responses: `{ success, message, data }`)

Auth (JWT Bearer for protected routes):
- `POST /api/auth/register` `{username,user_email,user_password,user_mobile?}` → `{token,user}`
- `POST /api/auth/login` `{user_email,user_password}` → `{token,user}` (verifies existing bcrypt)
- `GET /api/auth/me` · `PUT /api/auth/profile` · `POST /api/auth/logout`

Discovery:
- `GET /api/cities` · `GET /api/categories` · `GET /api/brands`
- `GET /api/shops?city=&category=&q=` · `GET /api/shops/{id}` (products+services+offers)
- `GET /api/products?category_id=&shop_id=&q=` · `GET /api/products/featured` · `GET /api/products/{id}`
- `GET /api/search?q=&city=`

Customer (auth):
- Wishlist: `GET /api/wishlist` · `POST /api/wishlist/{productId}` · `DELETE /api/wishlist/{productId}`
- Cart (one shop per cart): `GET /api/cart` · `POST /api/cart` (`409` conflict if another shop; send `force:true` to replace) · `PUT /api/cart/{cartId}` · `DELETE /api/cart/{cartId}` · `DELETE /api/cart`
- Orders: `POST /api/orders` · `GET /api/orders` · `GET /api/orders/{id}`
- Reviews: `POST /api/reviews`

Shopkeeper (auth, **server-side ownership enforced**):
- `GET /api/shops/mine`
- `POST /api/shops` (owner = logged-in user; app never sends owner_user_id)
- `PUT /api/shops/{id}` (403 unless owner)
- `POST /api/shops/{id}/products` (403 unless owner)
- `PUT /api/products/{id}` · `DELETE /api/products/{id}` (403 unless product's shop is owned)
- `GET /api/shops/{id}/orders` (403 unless owner)

Security: PDO prepared statements everywhere, JWT (HS256) validated server-side with
expiry, bcrypt via `password_verify`, generic auth errors, CORS, no DB creds in the app,
no raw SQL/PHP errors returned to clients.

---

## 5. App screens (all built & tested)

Splash → City select → Home (city, search, promo "List your shop in 2 minutes",
categories, local shops, featured products) → Bottom tabs **Home / Shops / Categories /
Orders / Account** → Shop details (cover, logo, verified, rating, Call/WhatsApp/Directions/
Share, Products/Services/Offers, business info) → Product details (gallery, price, sizes/
colours, reviews, Add to Cart, WhatsApp enquiry, View Shop) → Search → Wishlist → Cart
(one-shop rule) → Checkout (COD/Razorpay) → Order confirmation → Account (profile edit,
orders, wishlist) → **Shopkeeper**: Add Shop, Shop dashboard, Add/Edit product, Shop orders.

WhatsApp/Call/Directions use each shop's real stored number/address; enquiry messages are
prefilled with product/shop context and URL-encoded.

---

## 6. Preview harness note

The live preview on the Emergent platform can only run one API process and cannot run
PHP/MySQL, so a thin FastAPI adapter (`/app/backend`) serves the SAME JSON contract,
seeded with your real `buymark.sql` data in MongoDB, purely so you can see and test the
app now. It is a temporary testing layer — the production architecture is the PHP API in
this folder on your MySQL. Nothing about the app changes when you switch the base URL.

## 7. Known limitations
- Preview seed contains only your one real shop (Rahul Mans Wear) + real products — that
  is your actual data, not fake content. Add more shops via the app's Add Shop flow.
- Product image upload from the app uses a catalog picker in the preview; on your server
  wire the existing `insert_product.php` upload or an object store and store the filename.
- Razorpay: keep secret keys on your PHP server (as today); the checkout "Razorpay" option
  is where you plug your existing order-create/verify endpoints. COD works out of the box.
- Push notifications not included (was not requested).
