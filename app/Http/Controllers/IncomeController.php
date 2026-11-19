<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function franchise(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $franchise = Income::query()->where('type', Income::TYPE_FRANCHISE);
        $incomes = (clone $franchise)
            ->with(['user', 'order', 'fromUser'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->whereHas('order', function ($orders) use ($like): void {
                        $orders->where('number', 'like', $like)
                            ->orWhere('name', 'like', $like);
                    })->orWhereHas('user', function ($users) use ($like): void {
                        $users->where('name', 'like', $like)
                            ->orWhere('user_name', 'like', $like);
                    })->orWhereHas('fromUser', function ($customers) use ($like): void {
                        $customers->where('name', 'like', $like)
                            ->orWhere('user_name', 'like', $like);
                    });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.incomes.franchise', [
            'incomes' => $incomes,
            'search' => $search,
            'total' => (clone $franchise)->count(),
            'totalAmount' => round((float) (clone $franchise)->sum('amount'), 2),
        ]);
    }
}
