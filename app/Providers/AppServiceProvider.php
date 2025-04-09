<?php

namespace App\Providers;

use Laravel\Sanctum\Sanctum;
use App\Models\MongoDBNotification;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use App\Overrides\AuthToken\PersonalAccessToken;
use App\Overrides\Notifications\DatabaseNotification as NotificationsDatabaseNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $loader = AliasLoader::getInstance();
        // Use MongoDB-based token model
        $loader->alias(\Laravel\Sanctum\PersonalAccessToken::class, PersonalAccessToken::class);
        $loader->alias(DatabaseNotification::class, NotificationsDatabaseNotification::class);
        ;

    }
}