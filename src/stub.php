<?php

/**
 * Placeholder for `NewsBridge` used ONLY before `php artisan techbaripress:install` has downloaded the real
 * connector. Every static call is a safe no-op, so a site that already wires up
 * \NewsBridge::onPublish(...) / onFeed(...) / notifyPublished(...) in its own boot() code never fatals while the
 * connector is still missing. The moment the real news-bridge.php is present, that file defines NewsBridge instead
 * and this stub is never loaded.
 */
if (!class_exists('NewsBridge')) {
    class NewsBridge
    {
        /** onPublish/onFeed/onCategories/onApply/notifyPublished/status/connect … all become harmless no-ops. */
        public static function __callStatic($name, $arguments)
        {
            return null;
        }
    }
}
