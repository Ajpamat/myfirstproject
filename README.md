# TindaHub — PHP + XAMPP MVP

A local-first small-store management MVP built with plain PHP and MySQL/MariaDB. The **POS checkout is a separate screen/module** from inventory management.

## Included

- Separate owner accounts and store workspaces, with store-scoped users, products, sales, and sale items.
- Password-hashed registration/sign-in, separate owner/admin and cashier login pages with role checks, owner-managed cashier accounts, and CSRF protection on forms.
- Product inventory: name, SKU/barcode text, category, selling price, cost, stock quantity, low-stock threshold, search, and availability.
- Dedicated POS: product search, cart quantities, cash/card/e-wallet payment types, cash/change calculation, stock validation, and stock deduction on checkout.
- Sales history with receipt detail/printing, plus owner dashboard, low-stock list, simple reports, and CSV export.
- Responsive interface, PHP PDO prepared statements, and database transactions for checkout.

## Run it in XAMPP (you do these steps on your computer)

1. Install/start XAMPP and start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Copy the whole `tindahub` folder into XAMPP's web root, commonly:
   - Windows: `C:\xampp\htdocs\tindahub`
   - macOS: `/Applications/XAMPP/htdocs/tindahub`
   - Linux: `/opt/lampp/htdocs/tindahub`
3. Open phpMyAdmin (commonly `http://localhost/phpmyadmin`). Create a database named **`tindahub`**, using `utf8mb4` if asked.
4. Select the new database, choose **Import**, and import `database/schema.sql` from this project.
5. Open `includes/bootstrap.php` and check `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`. The default is MySQL host `127.0.0.1`, database `tindahub`, user `root`, and an empty password—the usual fresh XAMPP defaults. If you set a MySQL password, update `DB_PASS`.
6. Visit `http://localhost/tindahub/register.php`, create your store and owner account, then sign in at `admin-login.php`. Cashiers use `cashier-login.php`.
7. Add products from **Inventory**, then use the separate **POS** page to ring up sales.

> This project does not install or change XAMPP on your computer. You said you'll handle XAMPP manually; these steps are provided for when you copy the project there.

## Requirements

- PHP 8.1+ with PDO MySQL enabled (`pdo_mysql`). Typical XAMPP PHP builds include this.
- MySQL 5.7+/MariaDB 10.4+ with InnoDB enabled.
- Apache with PHP enabled. `.htaccess` files disable directory listings and block direct access to internal folders (requires Apache `AllowOverride` support).

## Project map

```text
assets/style.css           Responsive visual styles
includes/bootstrap.php     Database connection, sessions, auth/CSRF helpers
includes/layout.php        Shared page layout and navigation integration
includes/dock.php          Floating dock navigation and show/hide behavior
notifications_feed.php    Authenticated feed for new orders, POS sales, and low stock
database/schema.sql        MySQL/MariaDB schema
register.php               Store signup and owner account creation
admin-login.php / login.php Owner/admin sign-in
cashier-login.php          Cashier-only sign-in
index.php                  Dashboard
inventory.php              Inventory management (owner only)
pos.php                    Separate point of sale
sales.php                  Sales history and receipts
reports.php                Sales reports and CSV export (owner only)
team.php                   Add/deactivate cashier accounts (owner only)
profile.php                Update personal details, password, and appearance
```

## Notes and limits

- Prices and receipt currency are set to Philippine pesos (`₱`) and timezone to `Asia/Manila` in `includes/bootstrap.php`; edit these constants/timezone for your region.
- This is an MVP, not a hosted SaaS billing product. It does not include subscription payments, a platform-level admin console, email/SMS, supplier purchasing, loyalty points, or offline POS synchronization.
- Cash/card/e-wallet are recorded as payment types; no payment processor is connected.
- Each user belongs to one store, and application queries scope records to the signed-in store. Do not remove tenant filters when extending the app.
- For a public internet deployment, use HTTPS, non-default database credentials, regular backups, and a proper production hosting/security review. Do not expose this initial local-XAMPP configuration as a public service.
