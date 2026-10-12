<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function front(): View
    {
        return match (Template::activeKey()) {
            'ai' => app()->call([app(AiController::class), 'home']),
            'bootstrap' => app()->call([app(BootstrapController::class), 'home']),
            default => app()->call([app(ShopController::class), 'home']),
        };
    }

    public function index(): View
    {
        return view('templates.index', [
            'templates' => Template::query()->orderBy('id')->get(),
        ]);
    }

    public function update(Template $template): RedirectResponse
    {
        $template->activate();

        return redirect()->route('templates.index')->with('status', $template->name.' is the front page.');
    }
}
