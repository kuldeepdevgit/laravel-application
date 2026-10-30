<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $categories = Category::query()
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'total' => Category::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image'] = Category::storeImage($request->file('image'));

        Category::query()->create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category added.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $data['slug'] = $this->uniqueSlug($data['name'], $category);

        if ($request->hasFile('image')) {
            $previous = $category->image;
            $data['image'] = Category::storeImage($request->file('image'));
            $category->update($data);
            Category::deleteImageFile($previous);
        } else {
            $category->update($data);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category saved.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category deleted.');
    }

    /**
     * @return array{name: string, description: ?string, is_active: bool}
     */
    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categories', 'name')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:400'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'image' => [
                $category?->image ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'name.unique' => 'A category with this name already exists.',
            'image.required' => 'Choose an image and crop it before saving.',
            'image.image' => 'Upload a JPG, PNG, or WebP image.',
            'image.mimes' => 'Upload a JPG, PNG, or WebP image.',
            'image.max' => 'The image must be 4 MB or smaller.',
        ]);

        return [
            'name' => $data['name'],
            'description' => $request->filled('description') ? $data['description'] : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function uniqueSlug(string $name, ?Category $ignore = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'category';
        $slug = $base;
        $suffix = 2;

        while (Category::query()
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
