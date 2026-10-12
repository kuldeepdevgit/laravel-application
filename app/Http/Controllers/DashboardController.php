<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Support\Facades\Schema;
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
}
