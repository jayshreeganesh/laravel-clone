# Laravel Clone (Ultra-Low Inode MVC)

A lightweight Laravel 10/11 style MVC CRUD application crafted specifically for shared/free hosting environments (e.g. **InfinityFree**, **Aeon**, cPanel) where strict inode (file count) limits exist.

## 📊 Inode Stats
- **Total Files:** ~16 files
- **Total Inodes:** < 20
- **Standard Laravel Inodes:** 8,000 – 12,000+ files in `vendor/`
- **Savings:** **99.8% fewer inodes**

## 🚀 Directory Structure
```text
laravel-clone/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php         (Base Controller)
│   │       └── ProductController.php  (CRUD Controller)
│   └── Models/
│       └── Product.php                (Eloquent-style Active Record Model)
├── config/
│   └── database.php                   (Database connection config)
├── core/
│   └── Kernel.php                     (Route Facade, Eloquent Engine, Request Validator, View Engine)
├── public/
│   ├── index.php                      (Front Controller)
│   └── .htaccess
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.php                (Main Layout)
│       └── products/
│           ├── index.php
│           ├── create.php
│           ├── edit.php
│           └── show.php
├── routes/
│   └── web.php                        (Route::get, Route::post, Route::name)
├── index.php                          (Root fallback)
└── .htaccess
```

## 🛠 Features & Conventions
- **Routing:** `Route::get()`, `Route::post()`, named routes.
- **Eloquent ORM:** `Product::all()`, `Product::find($id)`, `Product::findOrFail($id)`, `Product::create($request->all())`, `$product->update()`, `$product->delete()`.
- **Validation & Request:** `$request->validate(['name' => 'required', 'price' => 'numeric'])` with automatic session flashing and `old('field')` repopulation.
- **Blade-like Views:** `view('products.index', compact('products'))` with slot layout rendering.
- **Database:** Auto-configured **SQLite** (0-setup) with instant toggle for **MySQL** in `config/database.php`.

## 💻 Local Testing
Run with PHP built-in server:
```bash
cd laravel-clone
php -S localhost:8000
```
Open `http://localhost:8000` in your browser.
