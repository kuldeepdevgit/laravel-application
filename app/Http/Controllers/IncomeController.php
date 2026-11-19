<?php

namespace App\Http\Controllers;

use App\Services\FranchiseIncomeCache;
use App\Services\FranchiseIncomeRow;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function franchise(Request $request, FranchiseIncomeCache $cache): View
    {
        $search = trim($request->string('q')->toString());
        $all = $cache->all();
        $filtered = $cache->matching($all, $search);

        return view('admin.incomes.franchise', [
            'incomes' => $cache->paginate($filtered, $request),
            'search' => $search,
            'total' => $all->count(),
            'totalAmount' => round((float) $all->sum(fn (FranchiseIncomeRow $row): float => $row->amountValue), 2),
        ]);
    }
}
