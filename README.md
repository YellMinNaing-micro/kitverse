# KitVerse

Football jersey e-commerce project using PHP, MariaDB/MySQL, and XAMPP.

## Local setup

1. Put this repository in `C:\xampp\htdocs\kitverse`.
2. Start Apache and MySQL in XAMPP.
3. On a fresh installation only, import `database/schema.sql` in phpMyAdmin. This export drops and recreates its tables, so do not import it over an existing database with data you want to keep.
4. Select `kitverse_db` in phpMyAdmin, then import `database/sample_catalog.sql` for example categories, leagues, clubs, products, and sizes. Import it only once into a fresh database.
5. Open `http://localhost:8080/kitverse/` (or use your Apache port).

`database/schema.sql` defines the database and its 11 tables. The sample catalog contains no users, orders, cart items, or payment records. Do not commit real customer data or credentials.

To create or reset a local admin login, run `C:\xampp\php\php.exe scripts\set_admin_password.php` from this folder. Enter an admin email and password when prompted. Keep the password private.

## Features

- Customer shop with search and league, club, and kit type filters
- Product sizes, stock, jersey personalization, guest cart, and account registration
- Checkout with cash on delivery or manually confirmed digital payments
- Order history and status tracking
- Admin product, size, stock, category, league, club, and order management

The generated hero and product concept images are under `assets/`. They are original visual placeholders for the sample catalog; they do not represent official club merchandise. Add real, licensed product images under `uploads/products/` and update product image paths in the admin panel when available.
