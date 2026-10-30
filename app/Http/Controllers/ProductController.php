<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $products = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('category', function ($category) use ($like): void {
                            $category->where('name', 'like', $like);
                        });
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'total' => Product::query()->count(),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new Product([
            'is_active' => true,
            'stock' => 0,
            'product_bv' => 0,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image'] = Product::storeImage($request->file('image'));

        Product::query()->create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return $this->formView($product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $data['slug'] = $this->uniqueSlug($data['name'], $product);

        if ($request->hasFile('image')) {
            $previous = $product->image;
            $data['image'] = Product::storeImage($request->file('image'));
            $product->update($data);
            Product::deleteImageFile($previous);
        } else {
            $product->update($data);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }

    private function formView(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => $this->categories(),
        ]);
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    /**
     * @return array{category_id: ?int, name: string, description: ?string, price: string, product_bv: string, compare_price: ?string, stock: int, is_active: bool}
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('products', 'name')->ignore($product?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2'],
            'product_bv' => ['required', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2', 'gt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'image' => [
                $product?->image ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'name.unique' => 'A product with this name already exists.',
            'compare_price.gt' => 'The compare price must be higher than the selling price.',
            'image.required' => 'Choose an image and crop it before saving.',
            'image.image' => 'Upload a JPG, PNG, or WebP image.',
            'image.mimes' => 'Upload a JPG, PNG, or WebP image.',
            'image.max' => 'The image must be 4 MB or smaller.',
        ]);

        return [
            'category_id' => $request->filled('category_id') ? (int) $data['category_id'] : null,
            'name' => $data['name'],
            'description' => $request->filled('description') ? $data['description'] : null,
            'price' => $data['price'],
            'product_bv' => $data['product_bv'],
            'compare_price' => $request->filled('compare_price') ? $data['compare_price'] : null,
            'stock' => (int) $data['stock'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function uniqueSlug(string $name, ?Product $ignore = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'product';
        $slug = $base;
        $suffix = 2;

        while (Product::query()
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
