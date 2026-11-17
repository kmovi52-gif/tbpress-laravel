<?php

namespace NewsBridge\Laravel;

use Illuminate\Support\ServiceProvider;
use NewsBridge\Laravel\Console\InstallCommand;

/**
 * Loads base_path('news-bridge.php') (written by `php artisan news-bridge:install`, with the site's API key baked in)
 * and adds its routes: /news-bridge, /epaper, /verify and /apply/{form}. No session, no CSRF: these are public
 * pages and signed server calls.
 */
class NewsBridgeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }

        $file = self::bridgeFile();
        if (!is_file($file)) {
            return; // not installed yet
        }
        require_once $file;

        $this->loadRoutesFrom(__DIR__ . '/../routes/bridge.php');
    }

    public static function bridgeFile(): string
    {
        return (string) (config('news-bridge.file') ?: base_path('news-bridge.php'));
    }
}
