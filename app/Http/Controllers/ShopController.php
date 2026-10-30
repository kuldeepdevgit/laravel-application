<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CartService;
use App\Support\Catalog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function home(CartService $cart): View
    {
        return view('store.home', [
            'departments' => $this->departments(),
            'deals' => Catalog::deals(),
            'popular' => Catalog::popular(),
            'more' => Catalog::more(),
            'dealEndsAt' => $cart->dealEndsAt(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $matches = Catalog::search([
            ...$filters,
            'cat' => $filters['match'],
        ]);
        $perPage = 9;
        $page = max(1, $request->integer('page', 1));

        $products = new LengthAwarePaginator(
            array_slice($matches, ($page - 1) * $perPage, $perPage),
            count($matches),
            $perPage,
            $page,
            ['path' => route('shop'), 'query' => $request->query()],
        );

        return view('store.shop', [
            'products' => $products,
            'filters' => $filters,
            'heading' => $this->heading($filters),
        ]);
    }

    public function show(string $product): View
    {
        $item = Catalog::find($product);

        abort_unless($item, 404);

        return view('store.product', [
            'product' => $item,
            'related' => Catalog::related($item),
            'reviews' => Catalog::reviews(),
        ]);
    }

    /**
     * @return Collection<int, Category>
     */
    private function departments(): Collection
    {
        if (! Schema::hasTable('categories')) {
            return new Collection;
        }

        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function department(string $value): ?Category
    {
        if ($value === '' || ! Schema::hasTable('categories')) {
            return null;
        }

        return Category::query()
            ->where('is_active', true)
            ->where(function ($query) use ($value): void {
                $query->where('slug', $value)->orWhere('name', $value);
            })
            ->first();
    }

    /**
     * @return array{q: string, cat: string, match: string, max: int, rated: bool, stock: bool, sort: string}
     */
    private function filters(Request $request): array
    {
        $category = $request->string('cat')->toString();
        $department = $this->department($category);
        $sort = $request->string('sort')->toString() ?: 'featured';
        $sorts = ['featured', 'price-asc', 'price-desc', 'rating', 'reviews'];
        $catalog = array_key_exists($category, Catalog::labels()) ? $category : '';

        return [
            'q' => $request->string('q')->trim()->toString(),
            'cat' => $department?->slug ?? $catalog,
            'match' => $department?->name ?? $catalog,
            'max' => min(200, max(10, $request->integer('max', 200))),
            'rated' => $request->boolean('rated'),
            'stock' => $request->boolean('stock'),
            'sort' => in_array($sort, $sorts, true) ? $sort : 'featured',
        ];
    }

    /**
     * @param  array{q: string, cat: string, match: string, max: int, rated: bool, stock: bool, sort: string}  $filters
     */
    private function heading(array $filters): string
    {
        if ($filters['match'] !== '') {
            return Catalog::label($filters['match']);
        }

        if ($filters['q'] !== '') {
            return 'Results for “'.$filters['q'].'”';
        }

        return 'All products';
    }
}
