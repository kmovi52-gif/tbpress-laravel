<?php

namespace NewsBridge\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use NewsBridge\Laravel\NewsBridgeServiceProvider;

/**
 *   php artisan techbaripress:install --key=YOUR_API_KEY --server=https://your-dashboard.example
 *
 * Downloads the connector (your key is already inside), clears the route cache and connects the site.
 */
class InstallCommand extends Command
{
    protected $aliases = ['news-bridge:install'];

    protected $signature = 'techbaripress:install
        {--key= : The API Key of this site (Site Connect page)}
        {--server= : Dashboard address, e.g. https://press.example.com}
        {--no-connect : Only write the file, do not connect now}';

    protected $description = 'Install and connect News Bridge (auto-posts, e-paper, ID verification, job applications)';

    public function handle(): int
    {
        $key = trim((string) ($this->option('key') ?: env('NEWS_BRIDGE_KEY', '')));
        $server = rtrim(trim((string) ($this->option('server') ?: env('NEWS_BRIDGE_SERVER', ''))), '/');
        if ($key === '' || !preg_match('#^https?://#i', $server)) {
            $this->error('API Key ও সার্ভারের ঠিকানা দিন:  php artisan techbaripress:install --key=tb_xxx --server=https://your-dashboard.com');

            return self::FAILURE;
        }

        $this->info('ব্রিজ ফাইল ডাউনলোড হচ্ছে…');
        try {
            $res = Http::timeout(60)->withHeaders(['X-Api-Key' => $key, 'Accept' => 'text/plain'])
                ->get($server . '/api/wp/bridge-file', ['platform' => 'laravel']);
        } catch (\Throwable $e) {
            $this->error('সার্ভারে পৌঁছানো যায়নি: ' . $e->getMessage());

            return self::FAILURE;
        }
        $code = (string) $res->body();
        if (!$res->successful() || strpos($code, 'class NewsBridge') === false) {
            $this->error('ডাউনলোড হয়নি: ' . ((string) ($res->json('message') ?: 'HTTP ' . $res->status())));

            return self::FAILURE;
        }

        $file = NewsBridgeServiceProvider::bridgeFile();
        file_put_contents($file, $code);
        $this->info('✔ ' . basename($file) . ' লেখা হয়েছে (' . number_format(strlen($code)) . ' বাইট)।');

        // routes of the package must be picked up (a cached route list would hide them)
        $this->call('route:clear');

        if ($this->option('no-connect')) {
            $this->line('ঠিক আছে। কানেক্ট করতে: ব্রাউজারে ' . rtrim((string) config('app.url'), '/') . '/news-bridge খুলে "কানেক্ট" চাপুন।');

            return self::SUCCESS;
        }

        $site = rtrim((string) config('app.url'), '/');
        if (!preg_match('#^https://#i', $site) || preg_match('#//(localhost|127\.|10\.|192\.168\.)#i', $site)) {
            $this->warn('APP_URL লাইভ https ঠিকানা নয় (' . $site . ') — তাই এখন কানেক্ট করা হলো না। সাইট লাইভে গেলে আবার চালান বা ব্রাউজারে /news-bridge খুলে কানেক্ট করুন।');

            return self::SUCCESS;
        }

        require_once $file;
        $this->info('সাইট কানেক্ট হচ্ছে…');
        $r = \NewsBridge::connect($site, $site . '/news-bridge');
        if (empty($r['ok'])) {
            $this->error('কানেক্ট হয়নি: ' . ($r['message'] ?? 'অজানা সমস্যা'));
            $this->line('সাইটটি ইন্টারনেট থেকে খোলা আছে কিনা, APP_URL ঠিক কিনা দেখে আবার চালান।');

            return self::FAILURE;
        }
        $this->info('✅ কানেক্ট হয়েছে। এখন ড্যাশবোর্ডে "সাইট টেস্ট" চালান।');

        return self::SUCCESS;
    }
}
