<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Template;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $template = Schema::hasTable('templates')
            ? Template::query()->where('is_active', true)->first()
            : null;

        return view('dashboard.index', [
            'template' => $template,
        ]);
    }

    public function section(Request $request, string $section): View
    {
        $pages = [
            'ref-link' => ['Ref Link', 'Share this link so new members join under your account.'],
            'manage-profile' => ['Manage Profile', 'Your account details are listed below.'],
            'edit-profile' => ['Edit Profile', 'Your account details are listed below.'],
            'change-password' => ['Change Password', 'Choose a new password for your account.'],
            'update-wallet-address' => ['Update Wallet Address', 'Save a USDT wallet, bank account, Google Pay, or PhonePe for your account.'],
            'team-report' => ['Team Report', 'Members who joined through your account will be listed here.'],
            'my-team' => ['My Team', 'Members who joined directly under your account will be listed here.'],
            'my-downline-team' => ['My Downline Team', 'Members in your downline are counted through '.User::DOWNLINE_DEPTH.' levels.'],
            'fund-management' => ['Fund Management', 'Fund activity for your account will be listed here.'],
            'fund-request-usdt' => ['Fund Request (USDT)', 'Request a USDT fund transfer for your account.'],
            'fund-request-list' => ['Fund Request List', 'Your fund requests will be listed here.'],
            'fund-from-admin' => ['Fund From Admin', 'Funds sent by admin will be listed here.'],
            'upgrade-list' => ['Upgrade List', 'Your upgrades will be listed here.'],
            'activation-upgrade' => ['Activation/ Upgrade', 'Activation and upgrade options for your account will be listed here.'],
            'fund-wallet' => ['Fund Wallet', 'Your fund wallet will be shown here.'],
            'income-report' => ['Income Report', 'Income recorded on your account will be listed here.'],
            'self-roi-income' => ['Self ROI Income', 'Your self ROI income will be listed here.'],
            'level-income-history' => ['Level Income History', 'Your level income history will be listed here.'],
            'level-roi-income-history' => ['Level ROI Income History', 'Your level ROI income history will be listed here.'],
            'booster-income' => ['Booster Income', 'Your booster income will be listed here.'],
            'life-time-reward' => ['Life Time Reward', 'Your lifetime rewards will be listed here.'],
            'salary-income' => ['Salary Income', 'Your salary income will be listed here.'],
            'withdrawal' => ['Withdrawal', 'Withdrawal requests for your account will be listed here.'],
            'withdrawal-list' => ['Withdrawal List', 'Your withdrawal requests will be listed here.'],
            'withdrawal-now' => ['Withdrawal Now', 'Request a withdrawal from your account.'],
            'support' => ['Support', 'Support requests for your account will be listed here.'],
            'document-list' => ['Document List', 'Documents shared with your account will be listed here.'],
            'order-history' => ['Order History', 'Orders you have placed in the shop are listed here.'],
        ];

        abort_unless(isset($pages[$section]), 404);

        [$heading, $intro] = $pages[$section];
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $referral = $section === 'ref-link' && filled($user->user_name)
            ? route('register', ['ref' => $user->user_name])
            : null;
        $history = $section === 'order-history'
            ? $this->orderHistory($request, $user)
            : ['orders' => null, 'orderSearch' => '', 'orderTotal' => 0];
        $members = match ($section) {
            'my-team' => $user->directMembers(),
            'my-downline-team' => $user->downlineMembers(),
            default => null,
        };

        return view('dashboard.section', [
            'heading' => $heading,
            'intro' => $intro,
            'section' => $section,
            'referral' => $referral,
            'user' => $user,
            'members' => $members,
            'downlineCounts' => $section === 'my-downline-team' && $members instanceof Collection
                ? $this->downlineLevelCounts($members)
                : null,
            'orders' => $history['orders'],
            'orderSearch' => $history['orderSearch'],
            'orderTotal' => $history['orderTotal'],
        ]);
    }

    public function order(Request $request, Order $order): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        $order->load(['items.product.category']);

        return view('dashboard.order', [
            'order' => $order,
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'zip_code' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return redirect()
            ->route('dashboard.section', 'edit-profile')
            ->with('status', 'Profile saved.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('dashboard.section', 'change-password')
            ->with('status', 'Password saved.');
    }

    public function updateWallet(Request $request): RedirectResponse
    {
        $request->merge([
            'usdt_bep20' => $this->nullableText($request, 'usdt_bep20'),
            'bank_name' => $this->nullableText($request, 'bank_name'),
            'account_name' => $this->nullableText($request, 'account_name'),
            'account_number' => $this->nullableText($request, 'account_number'),
            'ifsc_code' => $this->nullableIfsc($request),
            'google_pay' => $this->nullableUpiOrMobile($request, 'google_pay'),
            'phonepe' => $this->nullableUpiOrMobile($request, 'phonepe'),
        ]);

        $data = $request->validate([
            'usdt_bep20' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'bank_name' => ['nullable', 'string', 'max:120', 'required_with:account_name,account_number,ifsc_code'],
            'account_name' => ['nullable', 'string', 'max:120', 'required_with:bank_name,account_number,ifsc_code'],
            'account_number' => ['nullable', 'string', 'regex:/^[0-9]{6,20}$/', 'required_with:bank_name,account_name,ifsc_code'],
            'ifsc_code' => ['nullable', 'string', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/', 'required_with:bank_name,account_name,account_number'],
            'google_pay' => ['nullable', 'string', 'max:255', 'regex:/^(?:[6-9]\d{9}|[A-Za-z0-9.\-_]{2,256}@[A-Za-z][A-Za-z0-9]{1,63})$/'],
            'phonepe' => ['nullable', 'string', 'max:255', 'regex:/^(?:[6-9]\d{9}|[A-Za-z0-9.\-_]{2,256}@[A-Za-z][A-Za-z0-9]{1,63})$/'],
        ], [
            'usdt_bep20.regex' => 'Enter a USDT BEP 20 address.',
            'bank_name.required_with' => 'Enter a bank name.',
            'account_name.required_with' => 'Enter an account name.',
            'account_number.required_with' => 'Enter an account number.',
            'account_number.regex' => 'Enter 6 to 20 digits.',
            'ifsc_code.required_with' => 'Enter an IFSC code.',
            'ifsc_code.regex' => 'Enter a valid IFSC code.',
            'google_pay.regex' => 'Enter a Google Pay UPI ID or a 10-digit mobile number.',
            'phonepe.regex' => 'Enter a PhonePe UPI ID or a 10-digit mobile number.',
        ]);

        $request->user()->update([
            'usdt_bep20' => $data['usdt_bep20'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'account_name' => $data['account_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'google_pay' => $data['google_pay'] ?? null,
            'phonepe' => $data['phonepe'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.section', 'update-wallet-address')
            ->with('status', 'Wallet and bank details saved.');
    }

    private function nullableText(Request $request, string $key): ?string
    {
        $value = trim($request->string($key)->toString());

        return $value === '' ? null : $value;
    }

    private function nullableIfsc(Request $request): ?string
    {
        $value = strtoupper(trim($request->string('ifsc_code')->toString()));

        return $value === '' ? null : $value;
    }

    private function nullableUpiOrMobile(Request $request, string $key): ?string
    {
        $value = trim($request->string($key)->toString());

        if ($value === '') {
            return null;
        }

        $compact = preg_replace('/[\s\-]/', '', $value) ?? $value;

        if (preg_match('/^(?:\+91|91)?([6-9]\d{9})$/', $compact, $matches) === 1) {
            return $matches[1];
        }

        return $value;
    }

    /**
     * @param  Collection<int, User>  $members
     * @return array<int, int>
     */
    private function downlineLevelCounts(Collection $members): array
    {
        $counts = array_fill(1, User::DOWNLINE_DEPTH, 0);

        foreach ($members as $member) {
            $level = (int) $member->getAttribute('downline_level');

            if (array_key_exists($level, $counts)) {
                $counts[$level]++;
            }
        }

        return $counts;
    }

    /**
     * @return array{orders: LengthAwarePaginator<int, Order>, orderSearch: string, orderTotal: int}
     */
    private function orderHistory(Request $request, User $user): array
    {
        $search = trim($request->string('q')->toString());
        $orders = $user->orders()
            ->with(['items.product.category'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('number', 'like', $like)
                        ->orWhereHas('items', function ($items) use ($like): void {
                            $items->where('name', 'like', $like)
                                ->orWhere('sku', 'like', $like);
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return [
            'orders' => $orders,
            'orderSearch' => $search,
            'orderTotal' => $user->orders()->count(),
        ];
    }
}
