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
        // Jika folder public_html ada (kondisi di hosting cPanel), gunakan sebagai public_path (hanya di luar environment local dan jika index.php tersedia)
        if (!$this->app->environment('local') && is_dir(base_path('../public_html')) && file_exists(base_path('../public_html/index.php'))) {
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
            database_path('migrations/perdagangan'),
        ]);

        // Polyfill MySQL specific functions for SQLite environments
        $registerSqlitePolyfills = function ($pdo) {
            if ($pdo && method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction('DATE_FORMAT', function ($date, $format) {
                    if (!$date) return null;
                    $phpFormat = str_replace(
                        ['%Y', '%m', '%d', '%H', '%i', '%s', '%M', '%b'],
                        ['Y', 'm', 'd', 'H', 'i', 's', 'F', 'M'],
                        $format
                    );
                    return date($phpFormat, strtotime($date));
                });

                $pdo->sqliteCreateFunction('GREATEST', function (...$args) {
                    return !empty($args) ? max($args) : null;
                });

                $pdo->sqliteCreateFunction('FIELD', function ($value, ...$fields) {
                    $idx = array_search($value, $fields, true);
                    return $idx === false ? 0 : $idx + 1;
                });

                $pdo->sqliteCreateFunction('IF', function ($condition, $trueVal, $falseVal) {
                    return $condition ? $trueVal : $falseVal;
                });
            }
        };

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Database\Events\ConnectionEstablished::class, function ($event) use ($registerSqlitePolyfills) {
            if ($event->connection instanceof \Illuminate\Database\SQLiteConnection) {
                $registerSqlitePolyfills($event->connection->getPdo());
            }
        });

        if (\Illuminate\Support\Facades\DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            try {
                $registerSqlitePolyfills(\Illuminate\Support\Facades\DB::connection()->getPdo());
            } catch (\Throwable $e) {}
        }
    }
}
