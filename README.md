# E-Commerce Product Catalog System

A small online product catalog built with **PHP and MySQL**. Visitors can browse categories, search, sort and view product details. One administrator can log in and manage categories and products, including product images.

This is a mini project for **BCA 5th Semester, PHP with MySQL (PCA2)**.

> The system is a catalog only. It has no shopping cart, no payments and no customer accounts.

---

## Features

### For visitors
- Home page with all categories (with product counts) and featured products
- Browse the products of one category
- Keyword search in product name and description (partial words work)
- Sort by name (A to Z, Z to A) and by price (low to high, high to low)
- Pagination, 6 products per page
- Product detail page with name, image, category, description, price and stock status
- Clear "No products found" message when nothing matches
- Responsive layout for phones, tablets and desktops

### For the administrator
- Secure login and logout
- Dashboard with a summary of the catalog
- Add, edit and delete categories (a category that still has products cannot be deleted)
- Add, edit and delete products, with image upload (JPG, PNG or WEBP, maximum 2 MB)
- Server-side validation with clear error messages on every form

---

## Technology

| Part | Technology |
| --- | --- |
| Language | PHP (MySQLi) |
| Database | MySQL / MariaDB |
| Server | Apache (XAMPP) |
| Front end | HTML and CSS, no framework |
| Version control | Git and GitHub |

Tested in Chrome, Firefox and Edge.

---

## Setup (run it on your own computer)

**Requirements:** XAMPP (Apache, PHP and MySQL) and a modern browser such as Chrome, Firefox or Edge.

1. **Install XAMPP** and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. **Copy the project folder** into the XAMPP web folder, so that the path is:
   `C:\xampp\htdocs\ecommerce-product-catalog-php`
   The folder name must stay exactly `ecommerce-product-catalog-php`, because the pages use that path.
3. **Import the database:**
   - Open `http://localhost/phpmyadmin`
   - Click the **Import** tab (do not select a database first)
   - Choose the file `database/database.sql` and click **Import**
   - This creates the database `ecommerce_catalog` with 5 categories, 10 products and the admin account. The file can be imported again at any time.
4. **Open the site:** `http://localhost/ecommerce-product-catalog-php/`
5. **Open the admin area:** `http://localhost/ecommerce-product-catalog-php/admin/login.php`

The admin username and password are given separately with the project submission.

The database connection uses the XAMPP defaults (user `root`, no password). To change them, edit `config/db.php`.

---

## Project structure

```
ecommerce-product-catalog-php/
|-- index.php                  Home page
|-- products.php               Product list, search, sort and pagination
|-- product.php                Single product page
|-- admin/                     Administrator area
|   |-- auth.php               Session guard used by every admin page
|   |-- login.php, logout.php
|   |-- dashboard.php
|   |-- categories.php, category_edit.php, category_delete.php
|   |-- products.php, product_add.php, product_edit.php, product_delete.php
|   `-- menu.php
|-- includes/                  Shared parts: header, footer, helper functions, validation
|-- config/db.php              Database connection
|-- assets/
|   |-- css/style.css          Stylesheet
|   `-- images/products/       Product images (uploads)
|-- database/database.sql      Database structure and data
`-- docs/                      SRS document
```

---

## Database

Three tables:

| Table | Purpose |
| --- | --- |
| `categories` | Category name and description |
| `products` | Name, description, price, image, stock, featured flag, and the category it belongs to |
| `admin_users` | Administrator username and password hash |

One category has many products. The relation uses `ON DELETE RESTRICT`, so a category with products cannot be deleted.

---

## Security

- All database queries that use user input are **prepared statements**.
- All output is escaped with `htmlspecialchars()`, so typed HTML or scripts are shown as plain text.
- The administrator password is stored as a **hash** (`password_hash`) and checked with `password_verify`.
- Every admin page checks the login session, and the session ID is renewed at login.
- Image uploads are checked by size and by real file type (not by file name). Files are saved with random names, and script execution is blocked in the upload folder (`.htaccess`).
- If the database cannot be reached, only a short generic message is shown, without file paths or SQL.

### Known limitations
- No CSRF tokens on the admin forms.
- No limit on repeated wrong login attempts.
- The database user is `root` with an empty password, which is the XAMPP default. This must be changed for any real deployment.
- Keyword search uses `LIKE '%word%'`, which scans the table. This is fast at the tested size (with 1,010 products every page loaded in about 0.2 seconds or less). A FULLTEXT index is the recommended improvement for much larger catalogs.

---

## Testing

The project was tested against all 12 functional requirements of the SRS (FR-01 to FR-12), together with security, performance and responsive checks. All 67 recorded tests passed. Page load time was checked with 10 and with 1,010 products, and every page loaded in well under 3 seconds. The layout was checked from 320 px to 1920 px.

---

## Team

| Name | Assigned modules (per project roadmap) |
| --- | --- |
| Sayan Debnath | environment setup, database and shared layout, visitor pages (home, category listing, product details), admin login and session guard, admin menu and dashboard, category management, keyword search, sorting and pagination |
| Adrija Majumdar | admin product list, add, edit and delete product, secure image upload, validation review, sample images and responsive check |
| Both | Final joint work: cross-testing, security review, performance check, defect fixing, repository clean-up, database export, README and report |

Guided by: Proff. Majid Mallik Sir, Bhawanipur Global Campus

---

## Notes

Sample product images are used for demonstration only.
