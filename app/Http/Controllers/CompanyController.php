<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $companies = Company::query()
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.companies.index', [
            'companies' => $companies,
            'search' => $search,
            'total' => Company::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.companies.form', [
            'company' => new Company(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies', 'public');
        }

        Company::query()->create($data);

        return redirect()
            ->route('admin.companies.index')
            ->with('status', 'Company added.');
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.form', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validated($request, $company);
        $data['slug'] = $this->uniqueSlug($data['name'], $company);

        if ($request->hasFile('logo')) {
            $previous = $company->logo;
            $data['logo'] = $request->file('logo')->store('companies', 'public');
            $company->update($data);

            if (filled($previous)) {
                Storage::disk('public')->delete($previous);
            }
        } else {
            $company->update($data);
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('status', 'Company saved.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('status', 'Company deleted.');
    }

    /**
     * @return array{name: string, description: ?string, email: ?string, phone: ?string, address: ?string, website: ?string, is_active: bool}
     */
    private function validated(Request $request, ?Company $company = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('companies', 'name')->ignore($company?->id),
            ],
            'description' => ['nullable', 'string', 'max:400'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'name.unique' => 'A company with this name already exists.',
            'website.url' => 'Enter a full website address, including https://.',
            'logo.image' => 'Upload a JPG, PNG, or WebP image.',
            'logo.mimes' => 'Upload a JPG, PNG, or WebP image.',
            'logo.max' => 'The logo must be 4 MB or smaller.',
        ]);

        return [
            'name' => $data['name'],
            'description' => $request->filled('description') ? $data['description'] : null,
            'email' => $request->filled('email') ? $data['email'] : null,
            'phone' => $request->filled('phone') ? $data['phone'] : null,
            'address' => $request->filled('address') ? $data['address'] : null,
            'website' => $request->filled('website') ? $data['website'] : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function uniqueSlug(string $name, ?Company $ignore = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'company';
        $slug = $base;
        $suffix = 2;

        while (Company::query()
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
