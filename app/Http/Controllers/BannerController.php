<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $banners = Banner::query()
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('title', 'like', $like)
                        ->orWhere('eyebrow', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.banners.index', [
            'banners' => $banners,
            'search' => $search,
            'total' => Banner::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', [
            'banner' => new Banner([
                'is_active' => true,
                'sort_order' => ((int) Banner::query()->max('sort_order')) + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = Banner::storeImage($request->file('image'));

        Banner::query()->create($data);

        return redirect()
            ->route('admin.banners.index')
            ->with('status', 'Banner added.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', [
            'banner' => $banner,
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request, $banner);

        if ($request->hasFile('image')) {
            $data['image'] = Banner::storeImage($request->file('image'));
        }

        $banner->update($data);

        return redirect()
            ->route('admin.banners.index')
            ->with('status', 'Banner saved.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()
            ->route('admin.banners.index')
            ->with('status', 'Banner deleted.');
    }

    /**
     * @return array{eyebrow: ?string, title: string, description: ?string, button_label: ?string, button_url: ?string, is_active: bool, sort_order: int}
     */
    private function validated(Request $request, ?Banner $banner = null): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:280'],
            'button_label' => ['nullable', 'string', 'max:40', 'required_with:button_url'],
            'button_url' => ['nullable', 'string', 'max:255', 'required_with:button_label', 'regex:/^(https?:\/\/\S+|\/(?!\/)\S*)$/'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'image' => [
                $banner?->image ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
        ], [
            'title.required' => 'Enter a headline.',
            'button_label.required_with' => 'Enter the button label for this link.',
            'button_url.required_with' => 'Enter a link for this button.',
            'button_url.regex' => 'Use a path that starts with / or a full http(s) link.',
            'image.required' => 'Choose an image and crop it before saving.',
            'image.image' => 'Upload a JPG, PNG, or WebP image.',
            'image.mimes' => 'Upload a JPG, PNG, or WebP image.',
            'image.max' => 'The image must be 8 MB or smaller.',
        ]);

        return [
            'eyebrow' => $request->filled('eyebrow') ? $data['eyebrow'] : null,
            'title' => $data['title'],
            'description' => $request->filled('description') ? $data['description'] : null,
            'button_label' => $request->filled('button_label') ? $data['button_label'] : null,
            'button_url' => $request->filled('button_url') ? $data['button_url'] : null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) $data['sort_order'],
        ];
    }
}
