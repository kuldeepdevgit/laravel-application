<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Template;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
            'update-wallet-address' => ['Update Wallet Address', 'The wallet address on your account will be saved here.'],
            'team-report' => ['Team Report', 'Members who joined through your account will be listed here.'],
            'my-team' => ['My Team', 'Members who joined directly under your account will be listed here.'],
            'my-downline-team' => ['My Downline Team', 'Members in your downline will be listed here.'],
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

        return view('dashboard.section', [
            'heading' => $heading,
            'intro' => $intro,
            'section' => $section,
            'referral' => $referral,
            'user' => $user,
            'members' => $section === 'my-team' ? $user->directMembers() : null,
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
        $data = $request->validate([
            'usdt_bep20' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ], [
            'usdt_bep20.regex' => 'Enter a USDT BEP 20 address.',
        ]);

        $request->user()->update([
            'usdt_bep20' => $data['usdt_bep20'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.section', 'update-wallet-address')
            ->with('status', 'Wallet address saved.');
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
