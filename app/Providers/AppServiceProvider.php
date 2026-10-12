<?php

namespace App\Providers;

use App\Services\CartService;
use App\Support\Catalog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        View::composer('store.*', function ($view) {
            $view->with([
                'cartCount' => app(CartService::class)->count(),
                'categories' => Catalog::labels(),
                'navQuery' => request()->string('q')->toString(),
                'navCategory' => request()->string('cat')->toString(),
            ]);
        });
    }
}
