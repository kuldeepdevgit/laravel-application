<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'categoryCount' => Category::query()->count(),
            'companyCount' => Company::query()->count(),
        ]);
    }
}
