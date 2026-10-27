<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'categoryCount' => Category::query()->count(),
        ]);
    }
}
