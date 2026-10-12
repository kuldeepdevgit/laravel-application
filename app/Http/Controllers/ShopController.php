<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Support\Catalog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function home(CartService $cart): View
    {
        return view('store.home', [
            'deals' => Catalog::deals(),
            'popular' => Catalog::popular(),
            'more' => Catalog::more(),
            'dealEndsAt' => $cart->dealEndsAt(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $matches = Catalog::search($filters);
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
     * @return array{q: string, cat: string, max: int, rated: bool, stock: bool, sort: string}
     */
    private function filters(Request $request): array
    {
        $category = $request->string('cat')->toString();
        $sort = $request->string('sort')->toString() ?: 'featured';
        $sorts = ['featured', 'price-asc', 'price-desc', 'rating', 'reviews'];

        return [
            'q' => $request->string('q')->trim()->toString(),
            'cat' => array_key_exists($category, Catalog::labels()) ? $category : '',
            'max' => min(200, max(10, $request->integer('max', 200))),
            'rated' => $request->boolean('rated'),
            'stock' => $request->boolean('stock'),
            'sort' => in_array($sort, $sorts, true) ? $sort : 'featured',
        ];
    }

    /**
     * @param  array{q: string, cat: string, max: int, rated: bool, stock: bool, sort: string}  $filters
     */
    private function heading(array $filters): string
    {
        if ($filters['cat'] !== '') {
            return Catalog::label($filters['cat']);
        }

        if ($filters['q'] !== '') {
            return 'Results for “'.$filters['q'].'”';
        }

        return 'All products';
    }
}
