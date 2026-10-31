<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'productCount' => Product::query()->count(),
            'categoryCount' => Category::query()->count(),
            'companyCount' => Company::query()->count(),
            'bannerCount' => Banner::query()->count(),
        ]);
    }
}
