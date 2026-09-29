# KitVerse

Football jersey e-commerce project using PHP, MariaDB/MySQL, and XAMPP.

## Local setup

1. Put this repository in `C:\xampp\htdocs\kitverse`.
2. Start Apache and MySQL in XAMPP.
3. On a fresh installation only, import `database/schema.sql` in phpMyAdmin. This export drops and recreates its tables, so do not import it over an existing database with data you want to keep.
4. Select `kitverse_db` in phpMyAdmin, then import `database/sample_catalog.sql` for example categories, leagues, clubs, products, and sizes. Import it only once into a fresh database.
5. Open `http://localhost:8080/kitverse/` (or use your Apache port).

For an existing KitVerse database created before product discounts, import `database/migrations/2026-09-30-product-discounts.sql` once. Do not re-import `database/schema.sql` into an existing database; it drops tables.

`database/schema.sql` defines the database and its 11 tables. The sample catalog contains no users, orders, cart items, or payment records. Do not commit real customer data or credentials.

To create or reset a local admin login, run `C:\xampp\php\php.exe scripts\set_admin_password.php` from this folder. Enter an admin email and password when prompted. Keep the password private.

## Features

- Customer shop with search and league, club, and kit type filters
- Club browsing, five latest arrivals, and a sale view driven by product discounts
- Responsive homepage inspired by the supplied KitVerse visual reference, with a mobile menu and featured jersey cards
- Session wishlist and quick add buttons on product cards
- Product sizes, stock, jersey personalization, guest cart, and account registration
- Checkout with cash on delivery or manually confirmed digital payments
- Order history and status tracking
- Admin product, size, stock, category, league, club, and order management
- Product discounts from 0 to 90%, applied to cart and checkout prices
- Separate role-protected admin dashboard with sales, orders, customers, inventory, and payment views
- Admin product image upload and replacement: JPG, PNG or WebP, up to 8 MB, saved under `assets/images/{productId}/`

The generated hero and product concept images are under `assets/`. They are original visual placeholders for the sample catalog; they do not represent official club merchandise. Add your product photos through Admin → Products. Editing a product without choosing a new image keeps its current image; choosing a new one replaces it. Uploaded images are project files under `assets/images/`, so include them when deploying or backing up the catalog.
