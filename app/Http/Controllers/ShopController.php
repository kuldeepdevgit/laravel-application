<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Support\Catalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function home(): View
    {
        return view('store.home', [
            'banners' => $this->banners(),
            'showDefaultBanners' => $this->showDefaultBanners(),
            'departments' => $this->departments(),
            'products' => $this->products(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('store.shop', [
            'products' => $this->shopProducts($request, $filters),
            'filters' => $filters,
            'heading' => $this->heading($filters),
            'departments' => $this->shopDepartments(),
        ]);
    }

    public function show(string $product): View
    {
        $saved = $this->savedProduct($product);

        if ($saved) {
            return view('store.saved-product', [
                'product' => $saved,
                'related' => $this->relatedProducts($saved),
            ]);
        }

        $item = Catalog::find($product);

        abort_unless($item, 404);

        return view('store.product', [
            'product' => $item,
            'related' => Catalog::related($item),
            'reviews' => Catalog::reviews(),
        ]);
    }

    /**
     * @return Collection<int, Banner>
     */
    private function banners(): Collection
    {
        if (! Schema::hasTable('banners')) {
            return new Collection;
        }

        return Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function showDefaultBanners(): bool
    {
        if (! Schema::hasTable('banners')) {
            return true;
        }

        return Banner::query()->doesntExist();
    }

    private function savedProduct(string $slug): ?Product
    {
        if (! Schema::hasTable('products')) {
            return null;
        }

        return Product::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return Collection<int, Product>
     */
    private function relatedProducts(Product $product): Collection
    {
        return Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereKeyNot($product->id)
            ->when(
                $product->category_id,
                fn ($query) => $query->where('category_id', $product->category_id),
            )
            ->orderBy('name')
            ->limit(4)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    private function products(): Collection
    {
        if (! Schema::hasTable('products')) {
            return new Collection;
        }

        return Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
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
            ->where('is_featured', true)
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
     * @return Collection<int, Category>
     */
    private function shopDepartments(): Collection
    {
        if (! Schema::hasTable('categories')) {
            return new Collection;
        }

        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array{q: string, cat: string, match: string, category_id: int|null, max: int, ceiling: int, stock: bool, sort: string}  $filters
     */
    private function shopProducts(Request $request, array $filters): LengthAwarePaginator
    {
        $perPage = 9;

        if (! Schema::hasTable('products')) {
            return new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => route('shop'),
                'query' => $request->query(),
            ]);
        }

        $query = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $term = '%'.$filters['q'].'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->whereLike('name', $term)
                        ->orWhereLike('description', $term)
                        ->orWhereHas('category', fn (Builder $query) => $query->whereLike('name', $term));
                });
            })
            ->when(
                $filters['category_id'] !== null,
                fn (Builder $query) => $query->where('category_id', $filters['category_id']),
            )
            ->when(
                $filters['cat'] !== '' && $filters['category_id'] === null,
                fn (Builder $query) => $query->whereIn('id', []),
            )
            ->when(
                $filters['max'] < $filters['ceiling'],
                fn (Builder $query) => $query->where('price', '<=', $filters['max']),
            )
            ->when($filters['stock'], fn (Builder $query) => $query->where('stock', '>', 0));

        $this->applySort($query, $filters['sort']);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        if ($sort === 'price-asc') {
            $query->orderBy('price')->orderBy('name');

            return;
        }

        if ($sort === 'price-desc') {
            $query->orderByDesc('price')->orderBy('name');

            return;
        }

        $query->orderBy('name');
    }

    private function priceCeiling(): int
    {
        if (! Schema::hasTable('products')) {
            return 10;
        }

        $max = Product::query()->where('is_active', true)->max('price');

        return max(10, (int) ceil((float) ($max ?? 0)));
    }

    /**
     * @return array{q: string, cat: string, match: string, category_id: int|null, max: int, ceiling: int, stock: bool, sort: string}
     */
    private function filters(Request $request): array
    {
        $category = $request->string('cat')->toString();
        $department = $this->department($category);
        $sort = $request->string('sort')->toString() ?: 'featured';
        $sorts = ['featured', 'price-asc', 'price-desc'];
        $ceiling = $this->priceCeiling();
        $max = $request->has('max')
            ? min($ceiling, max(1, $request->integer('max')))
            : $ceiling;

        return [
            'q' => $request->string('q')->trim()->toString(),
            'cat' => $department?->slug ?? $category,
            'match' => $department?->name ?? ($category !== '' ? Catalog::label($category) : ''),
            'category_id' => $department?->id,
            'max' => $max,
            'ceiling' => $ceiling,
            'stock' => $request->boolean('stock'),
            'sort' => in_array($sort, $sorts, true) ? $sort : 'featured',
        ];
    }

    /**
     * @param  array{q: string, cat: string, match: string, category_id: int|null, max: int, ceiling: int, stock: bool, sort: string}  $filters
     */
    private function heading(array $filters): string
    {
        if ($filters['match'] !== '') {
            return $filters['match'];
        }

        if ($filters['q'] !== '') {
            return 'Results for “'.$filters['q'].'”';
        }

        return 'All products';
    }
}
