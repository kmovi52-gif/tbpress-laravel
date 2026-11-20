# টেকবাড়ি প্রেস — News Bridge for Laravel

One command connects a Laravel news site to the dashboard: auto-posts land in your site, `/epaper`, `/verify` and
`/apply/{id}` work on your own domain, and new posts can trigger photocards + social auto-post.

```bash
composer require techbaripress/laravel
php artisan techbaripress:install --key=YOUR_API_KEY --server=https://your-dashboard.example
```

* The API Key is on the dashboard's **Site Connect** page.
* The command downloads `news-bridge.php` (your key already inside) into the project root, clears the route cache and
  connects the site. The package then loads that file **and** registers `/news-bridge`, `/epaper`, `/verify`,
  `/apply/{form}` itself — you do **not** add `require_once base_path('news-bridge.php')` or the routes by hand.
* Save received posts: see "Laravel" on the Site Connect page (`NewsBridge::onPublish(...)` in `AppServiceProvider`), and
  call `NewsBridge::notifyPublished([...])` when you publish a post.

### Never crashes before install

The `NewsBridge` class is loaded in the provider's `register()` — before any other provider's `boot()` — so your own
`AppServiceProvider::boot()` can call `\NewsBridge::onPublish(...)` safely. Until `techbaripress:install` has run, a tiny
no-op stub stands in for the class, so the site never throws *"Class NewsBridge not found"*. Wrapping your calls in
`if (class_exists('NewsBridge'))` is still good practice and is what the Site Connect page shows.

No Composer? The manual way still works: download `news-bridge.php` from the Site Connect page and add the routes
shown there. Both ways use the same file.
