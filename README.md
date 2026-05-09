# Product Database Web App (PHP + MySQL)

A modular PHP + MySQL product management app with:

- CSV import to database (supports Chinese text)
- Full CRUD pages
- Column-based filtering
- Export all products or filtered results to CSV

---

## 1) Requirements

- PHP 8.1+ (CLI enabled)
- MySQL 5.7+ / 8.0+
- `mbstring` extension enabled in PHP

---

## 2) Project Structure

```text
product-database/
├── index.php
├── setup.sql
├── config/
│   ├── database.php
│   └── columns.php
├── core/
│   ├── Controller.php
│   ├── Database.php
│   └── Model.php
├── controllers/
│   ├── ImportController.php
│   └── ProductController.php
├── models/
│   └── Product.php
├── views/
│   ├── layout/
│   ├── import/
│   └── products/
└── public/css/app.css
```

---

## 3) Database Setup

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

---

## 4) Run Locally

In project root:

```powershell
cd "F:\外贸-WAYNE-TQB\网站\product-database"
php -S 127.0.0.1:3333 -t .
```

Open:

- <http://127.0.0.1:3333/>

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
- Import behavior is append-only (no overwrite)

### CRUD

- Product list: `?c=product&a=index`
- Create: `?c=product&a=create`
- View: `?c=product&a=show&id={id}`
- Edit: `?c=product&a=edit&id={id}`
- Delete: POST `?c=product&a=delete`

### Filtering

- List page supports filtering by multiple configured columns
- Filters are sent via query params (`f[field]=value`)

### CSV Export

- Export all products
- Export current filtered results
- Output uses UTF-8 BOM for better Excel compatibility with Chinese text

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

1. Add column in DB (`ALTER TABLE products ...`)
2. Add column mapping in `config/columns.php` (`field`, `label`, `type`, etc.)
3. Add field to `models/Product.php` `$fillable`

Because list/filter/form/import/export are driven by column config, new fields integrate quickly with minimal code changes.

---

## 8) Troubleshooting

### Server starts but page shows error

- Ensure MySQL is running
- Verify credentials in `config/database.php`
- Confirm `setup.sql` was executed successfully

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

---

## 9) Security/Production Notes

This project is designed for internal tooling and local deployment.
For production use, add:

- Authentication and role permissions
- CSRF protection
- Input validation hardening
- Nginx/Apache + PHP-FPM deployment
- Backup and audit logging
