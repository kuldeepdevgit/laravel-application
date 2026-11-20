<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\LevelIncomeHistory;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $orders = Order::query()
            ->with(['items.product.category', 'user', 'payment'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('number', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhereHas('items', function ($items) use ($like): void {
                            $items->where('name', 'like', $like)
                                ->orWhere('sku', 'like', $like);
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'search' => $search,
            'total' => Order::query()->count(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product.category', 'user', 'payment', 'franchiseIncome']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected'])],
        ]);

        DB::transaction(function () use ($data, $order): void {
            $order->update([
                'status' => $data['status'],
            ]);

            if ($data['status'] === 'confirmed') {
                $order->user?->markPaidOnFirstApproval();
                Income::creditFranchiseFor($order);
                LevelIncomeHistory::creditFor($order);
            }
        });

        return back()->with('status', $order->number.' is now '.$order->statusLabel().'.');
    }
}
