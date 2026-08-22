<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Jika folder public_html ada (kondisi di hosting cPanel), gunakan sebagai public_path
        if (is_dir(base_path('../public_html'))) {
            $this->app->usePublicPath(base_path('../public_html'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        \Carbon\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID', 'id_ID.utf8', 'Indonesian');

        // Register subfolder migrations
        $this->loadMigrationsFrom([
            database_path('migrations/wifi'),
        ]);
    }
}
