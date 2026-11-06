<?php

namespace App\Http\Controllers;

use App\Models\FundAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FundController extends Controller
{
    public function requests(Request $request): View
    {
        return view('admin.funds.requests', [
            'search' => trim($request->string('q')->toString()),
        ]);
    }

    public function address(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $addresses = FundAddress::query()
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('wallet_address', 'like', $like)
                        ->orWhere('bank_name', 'like', $like)
                        ->orWhere('account_name', 'like', $like)
                        ->orWhere('account_number', 'like', $like)
                        ->orWhere('ifsc_code', 'like', $like);
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.funds.address', [
            'addresses' => $addresses,
            'search' => $search,
            'total' => FundAddress::query()->count(),
        ]);
    }

    public function createAddress(): View
    {
        return view('admin.funds.address-form', [
            'address' => new FundAddress(['address_type' => 'usdt']),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $this->validatedAddress($request);

        if ($request->hasFile('wallet_screenshot')) {
            $data['wallet_screenshot'] = $request->file('wallet_screenshot')->store('fund-addresses', 'public');
        }

        FundAddress::query()->create($data);

        return redirect()
            ->route('admin.funds.address')
            ->with('status', 'Fund address added.');
    }

    public function editAddress(FundAddress $fundAddress): View
    {
        return view('admin.funds.address-form', [
            'address' => $fundAddress,
        ]);
    }

    public function updateAddress(Request $request, FundAddress $fundAddress): RedirectResponse
    {
        $data = $this->validatedAddress($request, $fundAddress);
        $previous = $fundAddress->wallet_screenshot;

        if ($data['address_type'] === 'usdt' && $request->hasFile('wallet_screenshot')) {
            $data['wallet_screenshot'] = $request->file('wallet_screenshot')->store('fund-addresses', 'public');
            $fundAddress->update($data);

            if (filled($previous)) {
                Storage::disk('public')->delete($previous);
            }
        } elseif ($data['address_type'] === 'inr') {
            $data['wallet_screenshot'] = null;
            $fundAddress->update($data);

            if (filled($previous)) {
                Storage::disk('public')->delete($previous);
            }
        } else {
            $fundAddress->update($data);
        }

        return redirect()
            ->route('admin.funds.address')
            ->with('status', 'Fund address saved.');
    }

    public function destroyAddress(FundAddress $fundAddress): RedirectResponse
    {
        $fundAddress->delete();

        return redirect()
            ->route('admin.funds.address')
            ->with('status', 'Fund address deleted.');
    }

    public function send(): View
    {
        return view('admin.funds.send');
    }

    public function storeSend(): RedirectResponse
    {
        return back()->with('status', 'The send form is on screen. Sending is not connected yet.');
    }

    public function history(Request $request): View
    {
        return view('admin.funds.history', [
            'search' => trim($request->string('q')->toString()),
        ]);
    }

    /**
     * @return array{address_type: string, wallet_address: ?string, bank_name: ?string, account_name: ?string, account_number: ?string, ifsc_code: ?string}
     */
    private function validatedAddress(Request $request, ?FundAddress $address = null): array
    {
        $request->merge([
            'wallet_address' => trim($request->string('wallet_address')->toString()),
            'ifsc_code' => strtoupper(trim($request->string('ifsc_code')->toString())),
        ]);

        $data = $request->validate([
            'address_type' => ['required', Rule::in(['inr', 'usdt'])],
            'wallet_address' => ['exclude_unless:address_type,usdt', 'required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'wallet_screenshot' => [
                'exclude_unless:address_type,usdt',
                Rule::requiredIf($request->input('address_type') === 'usdt' && ! filled($address?->wallet_screenshot)),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'bank_name' => ['exclude_unless:address_type,inr', 'required', 'string', 'max:120'],
            'account_name' => ['exclude_unless:address_type,inr', 'required', 'string', 'max:120'],
            'account_number' => ['exclude_unless:address_type,inr', 'required', 'string', 'regex:/^[0-9]{6,20}$/'],
            'ifsc_code' => ['exclude_unless:address_type,inr', 'required', 'string', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
        ], [
            'wallet_address.required' => 'Enter a wallet address.',
            'wallet_address.regex' => 'Enter a USDT BEP 20 address.',
            'wallet_screenshot.required' => 'Upload a wallet screenshot.',
            'wallet_screenshot.image' => 'Upload a JPG, PNG, or WebP image.',
            'wallet_screenshot.mimes' => 'Upload a JPG, PNG, or WebP image.',
            'wallet_screenshot.max' => 'The screenshot must be 4 MB or smaller.',
            'bank_name.required' => 'Enter a bank name.',
            'account_name.required' => 'Enter an account name.',
            'account_number.required' => 'Enter an account number.',
            'account_number.regex' => 'Enter 6 to 20 digits.',
            'ifsc_code.required' => 'Enter an IFSC code.',
            'ifsc_code.regex' => 'Enter a valid IFSC code.',
        ]);

        if ($data['address_type'] === 'usdt') {
            return [
                'address_type' => 'usdt',
                'wallet_address' => $data['wallet_address'],
                'bank_name' => null,
                'account_name' => null,
                'account_number' => null,
                'ifsc_code' => null,
            ];
        }

        return [
            'address_type' => 'inr',
            'wallet_address' => null,
            'bank_name' => $data['bank_name'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'ifsc_code' => $data['ifsc_code'],
        ];
    }
}
