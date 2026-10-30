<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use App\Support\Catalog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
            $requested = request()->string('cat')->toString();
            $navCategory = $requested;

            if ($requested !== '' && Schema::hasTable('categories')) {
                $name = Category::query()->where('is_active', true)->where('slug', $requested)->value('name');

                if (is_string($name) && array_key_exists($name, Catalog::labels())) {
                    $navCategory = $name;
                }
            }

            $view->with([
                'cartCount' => app(CartService::class)->count(),
                'categories' => Catalog::labels(),
                'navQuery' => request()->string('q')->toString(),
                'navCategory' => $navCategory,
            ]);
        });
    }
}
