<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\UserPackageQualification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $packages = Package::query()
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where('package_name', 'like', $like);
            })
            ->orderBy('package_amount')
            ->orderBy('package_name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'search' => $search,
            'total' => Package::query()->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', [
            'package' => new Package(['package_status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Package::query()->create($this->validated($request));

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'Package added.');
    }

    public function edit(Package $package): View
    {
        return view('admin.packages.form', [
            'package' => $package,
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $package->update($this->validated($request, $package));

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'Package saved.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $package->delete();

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'Package deleted.');
    }

    public function history(): View
    {
        return view('admin.packages.history');
    }

    public function qualifications(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $qualifications = UserPackageQualification::query()
            ->where('is_qualified', true)
            ->with(['user', 'package'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->whereHas('user', function ($users) use ($like): void {
                        $users->where('name', 'like', $like)
                            ->orWhere('user_name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    })->orWhereHas('package', function ($packages) use ($like): void {
                        $packages->where('package_name', 'like', $like);
                    });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.packages.qualifications', [
            'qualifications' => $qualifications,
            'search' => $search,
            'total' => UserPackageQualification::query()->where('is_qualified', true)->count(),
        ]);
    }

    /**
     * @return array{package_name: string, package_amount: string, package_bv: string, direct_bv: string, package_status: string}
     */
    private function validated(Request $request, ?Package $package = null): array
    {
        $data = $request->validate([
            'package_name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('package', 'package_name')->ignore($package?->id),
            ],
            'package_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'direct_bv' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'package_status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'package_name.required' => 'Enter a package name.',
            'package_name.unique' => 'A package with this name already exists.',
            'package_amount.required' => 'Enter a package amount.',
            'package_amount.numeric' => 'Enter a package amount.',
            'package_amount.min' => 'Enter a package amount of 0 or more.',
            'package_amount.decimal' => 'Use up to two decimal places.',
            'direct_bv.required' => 'Enter the direct selling BV.',
            'direct_bv.numeric' => 'Enter the direct selling BV.',
            'direct_bv.min' => 'Enter a direct selling BV of 0 or more.',
            'direct_bv.decimal' => 'Use up to two decimal places.',
            'package_status.required' => 'Choose a status.',
            'package_status.in' => 'Choose a status.',
        ]);

        $amount = number_format((float) $data['package_amount'], 2, '.', '');

        return [
            'package_name' => $data['package_name'],
            'package_amount' => $amount,
            'package_bv' => $amount,
            'direct_bv' => number_format((float) $data['direct_bv'], 2, '.', ''),
            'package_status' => $data['package_status'],
        ];
    }
}
