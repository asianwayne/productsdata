# Product Database Web App (PHP + MySQL)

A modular PHP + MySQL product management app with:

- CSV import to database (supports Chinese text)
- **Product image module** — upload on create/edit, or bulk-attach via CSV import by matching the `TQB编码` filename
- Full CRUD pages
- Column-based filtering and global search
- Export all products or filtered results to CSV
- Category management
- OEM matching tool
- Authentication (first-run admin setup)

---

## 1) Requirements

- PHP 8.1+ (CLI enabled)
- MySQL 5.7+ / 8.0+
- PHP extensions: `mbstring`, `pdo_mysql`, `fileinfo`, `gd` (optional, only for future image processing)

---

## 2) Project Structure

```text
product-database/
├── index.php                 # front controller (routing: ?c=...&a=...)
├── setup.sql                 # first-time DB setup
├── migrations/
│   └── 001_add_image_path.sql  # add image_path column to existing DBs
├── config/
│   ├── database.php
│   └── columns.php           # single source of truth for columns
├── core/
│   ├── Controller.php
│   ├── Database.php
│   ├── Model.php
│   └── ImageHelper.php       # image upload / validate / save / delete
├── controllers/
│   ├── AuthController.php
│   ├── CategoryController.php
│   ├── ImportController.php
│   ├── MatchController.php
│   └── ProductController.php
├── models/
│   ├── Category.php
│   ├── Product.php
│   └── User.php
├── views/
│   ├── auth/
│   ├── categories/
│   ├── import/
│   ├── layout/
│   ├── match/
│   └── products/
└── public/
    ├── css/app.css
    └── uploads/
        └── products/         # uploaded product images live here
```

---

## 3) Database Setup

### First-time install

1. Start MySQL service.
2. Run `setup.sql` in your MySQL client:

```sql
source F:/外贸-WAYNE-TQB/网站/product-database/setup.sql;
```

3. Edit DB credentials in `config/database.php`:

```php
return [
    'host'     => 'localhost',
    'port'     => 3306,
    'dbname'   => 'product_database',
    'username' => 'root',
    'password' => '', // update this
    'charset'  => 'utf8mb4',
];
```

4. On first visit the app detects an empty `users` table and redirects to a one-time setup page to create the administrator account.

### Upgrading an existing database

If you already have a populated `products` table and just need to add the new image column, run the migration once:

```sql
USE product_database;
SOURCE migrations/001_add_image_path.sql;
```

This adds a single nullable `image_path VARCHAR(255)` column after `warehouse_a`. Existing data is untouched.

---

## 4) Run Locally

In project root:

```powershell
cd "F:\外贸-WAYNE-TQB\网站\product-database"
php -S 127.0.0.1:3333 -t .
```

Open:

- <http://127.0.0.1:3333/>

The first request will redirect to the login (or the admin-setup page if no users exist yet).

---

## 5) Main Features

### CSV Import

- Entry: `?c=import&a=index`
- Upload `.csv` file
- Encoding option:
  - Auto detect (recommended)
  - GBK / GB2312
  - UTF-8
- Header names must match configured Chinese labels in `config/columns.php`
- Import behavior:
  - Rows with a **new TQB编码** → inserted
  - Rows with an **existing TQB编码** and a strict-subset OEM号码 (and no image to attach) → skipped
  - Rows with an **existing TQB编码** and additional OEM号码 → merged into the existing OEM (slash-separated, deduplicated) and other fields are updated

### Product Image Module

Each product can carry one image. The image is stored under `public/uploads/products/` and the DB stores its relative path in `products.image_path`.

**Add / Edit product page** (`?c=product&a=create` or `?c=product&a=edit&id={id}`)

- An image upload field with live preview
- On edit, a "**删除当前图片**" checkbox to wipe the existing image, plus the option to replace it by uploading a new one
- Allowed types: `jpg`, `jpeg`, `png`, `gif`, `webp`; max 8 MB per file
- File contents are MIME-sniffed via `finfo` to defend against renamed binaries

**Bulk attach via CSV import**

When you upload a CSV, the import page also has an optional **产品图片** multi-file input:

1. Put your product images in the `images/` folder that sits **next to** the CSV file (the same way users normally organize them).
2. Each image file must be named after the row's `TQB编码`, e.g. `TQB0-0002.jpg` (the extension can be `.jpg/.jpeg/.png/.gif/.webp`, matching is case-insensitive).
3. On the import page:
   - Pick the CSV file as usual
   - In the **产品图片(可选)** input, select all images from the `images/` folder
   - Tip: tick **"改为「选择整个文件夹」"** (Chrome / Edge) so you can pick the whole folder in one click via `webkitdirectory`
4. Submit. For each CSV row, the importer:
   - Computes the lower-cased TQB code key
   - Looks it up in the uploaded-image map
   - If found, replaces any existing image and writes the new path into `image_path`
   - If the row was about to be skipped (subset OEM) but a new image arrived, the row is updated **just to attach the image**
5. After import, the result screen shows:
   - 上传 N 张 / 成功匹配 M 张
   - An expandable list of TQB codes that appeared in the CSV but had no matching image
   - Any images that did NOT match any TQB code are deleted from disk to avoid orphan files

**Display**

- Product list (`?c=product&a=index`) shows a 48×48 thumbnail per row (clickable for full-size)
- Product detail (`?c=product&a=show&id={id}`) shows a larger image card at the top
- Recent-import preview on the import page also shows the attached image thumbnail

### CRUD

- Product list: `?c=product&a=index`
- Create: `?c=product&a=create` (supports image upload)
- View: `?c=product&a=show&id={id}`
- Edit: `?c=product&a=edit&id={id}` (supports image upload / replace / remove)
- Delete: POST `?c=product&a=delete` (also removes the linked image file)
- Delete just the image: POST `?c=product&a=deleteImage&id={id}`
- Clear ALL: POST `?c=product&a=deleteAll` (truncates the table and clears all linked image files)

### Filtering & Search

- Global search box on the list page (searches across all fillable fields)
- Multi-column filter panel; filters are sent as `f[field]=value` query params

### CSV Export

- Export all products
- Export current filtered results
- Output uses UTF-8 BOM for better Excel compatibility with Chinese text
- Note: the exported CSV does **not** embed images; only the configured product columns are written.

---

## 6) Chinese Text / Encoding Notes

To avoid garbled Chinese text:

- Keep MySQL/table charset as `utf8mb4`
- Keep PHP output charset as UTF-8
- Use the import page encoding selector correctly
- Exported CSV already includes UTF-8 BOM

---

## 7) Extend Columns (Secondary Development)

To add a new product column:

1. Add column in DB (`ALTER TABLE products ...`) — and add a matching file under `migrations/` if you ship this to other environments
2. Add column mapping in `config/columns.php` (`field`, `label`, `type`, `filterable`, `list`, `tab`)
3. Add the field to `models/Product.php` `$fillable`

Because list / filter / form / import / export are driven by column config, new fields integrate quickly with minimal code changes.

The image column (`image_path`) is intentionally NOT in `config/columns.php` because it is not a CSV column; it is handled directly by `ProductController` and `ImportController` via `core/ImageHelper.php`.

---

## 8) Upload Limits

The CSV importer accepts the CSV file plus an optional batch of image files in the same POST. If you bundle hundreds of images, you may hit PHP defaults — adjust in `php.ini`:

```ini
upload_max_filesize = 64M
post_max_size       = 128M
max_file_uploads    = 500
memory_limit        = 256M
max_execution_time  = 300
```

Restart PHP / your dev server afterwards.

---

## 9) Troubleshooting

### Server starts but page shows error

- Ensure MySQL is running
- Verify credentials in `config/database.php`
- Confirm `setup.sql` was executed successfully
- If you upgraded from an older version, run `migrations/001_add_image_path.sql`

### `php -S localhost:3333` not reachable

- Try binding explicitly to IPv4:

```powershell
php -S 127.0.0.1:3333 -t .
```

- Check firewall/port conflict
- Try another port (e.g. `8080`)

### CSV import fails

- Ensure first row is header row
- Ensure file extension is `.csv`
- Use Auto/GBK option for Excel-exported Chinese CSV files

### Images not appearing after import

- Filename (without extension) must equal the `TQB编码` cell value (case-insensitive)
- Allowed extensions: `jpg`, `jpeg`, `png`, `gif`, `webp`
- Confirm the `public/uploads/products/` directory exists and is writable by the PHP process
- Check the result banner: it shows how many images were uploaded vs. matched, and lists TQB codes with no matching image
- Hitting PHP upload limits? See section 8 above

### Uploaded image fails validation

- File must be a real image (MIME is sniffed via `finfo`); a `.jpg` extension on a non-image file will be rejected
- Max size: 8 MB per file

---

## 10) Security / Production Notes

This project is designed for internal tooling and local deployment.
For production use, add:

- HTTPS termination
- Stronger authentication (e.g. 2FA) and role permissions
- CSRF protection on all POST forms (image uploads / deletes included)
- Input validation hardening
- Nginx/Apache + PHP-FPM deployment
- Disable directory listing of `public/uploads/products/` (an `index.html` placeholder is already in place; configure your webserver to deny `Indexes`)
- Serve uploaded images through a CDN or with proper `Cache-Control` headers
- Backup `public/uploads/products/` together with the database
- Audit logging
