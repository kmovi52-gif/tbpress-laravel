<?php

namespace NewsBridge\Laravel;

use Illuminate\Support\ServiceProvider;
use NewsBridge\Laravel\Console\InstallCommand;

/**
 * Loads base_path('news-bridge.php') (written by `php artisan techbaripress:install`, with the site's API key baked in)
 * and adds its routes: /news-bridge, /epaper, /verify and /apply/{form}. No session, no CSRF: these are public
 * pages and signed server calls.
 *
 * The connector is required in register() — before ANY provider's boot() — so a site's own boot() code can safely
 * call \NewsBridge::onPublish(...) without worrying about provider order. Before the first install the file does not
 * exist yet; a tiny no-op stub is loaded instead so that code never fatals ("Class NewsBridge not found").
 */
class NewsBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $file = self::bridgeFile();
        if (is_file($file)) {
            require_once $file;          // the real connector (key inside)
        } elseif (!class_exists('NewsBridge')) {
            require_once __DIR__ . '/stub.php'; // not installed yet: safe no-op so the site never crashes
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }

        // only serve the bridge pages once the real connector is installed (the stub has no routes to back them)
        if (is_file(self::bridgeFile())) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/bridge.php');
        }
    }

    public static function bridgeFile(): string
    {
        return (string) (config('news-bridge.file') ?: base_path('news-bridge.php'));
    }
}
